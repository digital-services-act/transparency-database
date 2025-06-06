#!/usr/bin/env python3

import os
import logging
import threading
import time
from logging.handlers import RotatingFileHandler
from flask import Flask, request, jsonify
from kafka import KafkaProducer
from kafka.errors import KafkaError
from dotenv import load_dotenv

# Configure logging with file rotation
log_dir = 'storage/logs'
os.makedirs(log_dir, exist_ok=True)
log_file = os.path.join(log_dir, 'kafka_forwarder.log')

handler = RotatingFileHandler(log_file, maxBytes=10485760, backupCount=10)
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[handler, logging.StreamHandler()]  # Log to both file and console
)
logger = logging.getLogger("kafka_forwarder")

# Load environment variables from .env
load_dotenv()

# Configuration
PORT = int(os.getenv("KAFKA_FORWARDER_PORT", "6666"))
KAFKA_BROKERS = os.getenv("KAFKA_BROKERS", "localhost:9092").split(",")
KAFKA_TOPIC = os.getenv("KAFKA_TOPIC", "transparency_statements")
CERT_PATH = os.getenv("KAFKA_CERT_PATH", "kafka-service.cert")  # Path to SSL cert
KEY_PATH = os.getenv("KAFKA_KEY_PATH", "kafka-service.key")    # Path to SSL key
KAFKA_TIMEOUT = int(os.getenv("KAFKA_TIMEOUT", "10"))  # Timeout in seconds

# Metrics
message_count = 0
error_count = 0

# Initialize Flask app
app = Flask(__name__)

# Create a thread lock to synchronize access to the Kafka producer
producer_lock = threading.Lock()
producer = None

def create_producer():
    """Create and return a Kafka producer"""
    global producer
    
    try:
        new_producer = KafkaProducer(
            bootstrap_servers=KAFKA_BROKERS,
            security_protocol="SSL",
            ssl_certfile=CERT_PATH,
            ssl_keyfile=KEY_PATH,
            # Add retry configuration
            retries=5,
            retry_backoff_ms=500
        )
        logger.info(f"Successfully connected to Kafka brokers: {KAFKA_BROKERS}")
        logger.info(f"Using Kafka topic: {KAFKA_TOPIC}")
        return new_producer
    except Exception as e:
        logger.error(f"Failed to connect to Kafka: {str(e)}")
        return None

def ensure_producer():
    """Ensure we have a working producer, create one if not"""
    global producer
    
    if producer is None:
        producer = create_producer()
    return producer is not None

# Create initial producer
ensure_producer()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    health_status = {
        'status': 'healthy' if ensure_producer() else 'unhealthy',
        'kafka_connected': producer is not None,
        'messages_processed': message_count,
        'errors': error_count
    }
    
    status_code = 200 if health_status['kafka_connected'] else 503
    return jsonify(health_status), status_code

@app.route('/send', methods=['POST'])
def send_to_kafka():
    """Handle POST requests and forward raw payload to Kafka"""
    global message_count, error_count
    
    try:
        # Get raw data from the request
        raw_data = request.get_data()
        
        if not raw_data:
            error_count += 1
            return jsonify({'status': 'error', 'message': 'Empty request body'}), 400
        
        # Acquire lock to safely use the producer
        with producer_lock:
            if not ensure_producer():
                error_count += 1
                return jsonify({'status': 'error', 'message': 'Kafka producer not available'}), 500
            
            # Send the raw message to Kafka
            future = producer.send(KAFKA_TOPIC, value=raw_data)
            
            # Wait for the message to be sent
            record_metadata = future.get(timeout=KAFKA_TIMEOUT)
            
            message_count += 1
            logger.info(f"Message sent to Kafka: topic={KAFKA_TOPIC}, partition={record_metadata.partition}, offset={record_metadata.offset}")
            
            # Return success response
            return jsonify({
                'status': 'success',
                'topic': KAFKA_TOPIC,
                'partition': record_metadata.partition,
                'offset': record_metadata.offset
            }), 200
    
    except KafkaError as ke:
        error_count += 1
        logger.error(f"Kafka error sending message: {str(ke)}")
        
        # Try to reconnect on Kafka errors
        with producer_lock:
            if producer:
                try:
                    producer.close(timeout=5)
                except:
                    pass
                producer = None
                ensure_producer()
                
        return jsonify({'status': 'error', 'message': f"Kafka error: {str(ke)}"}), 500
        
    except Exception as e:
        error_count += 1
        logger.error(f"Error sending message to Kafka: {str(e)}")
        return jsonify({'status': 'error', 'message': f"Error: {str(e)}"}), 500

# Watchdog thread to ensure Kafka connection stays alive
def connection_watchdog():
    while True:
        time.sleep(60)  # Check every 60 seconds
        with producer_lock:
            ensure_producer()

# Graceful shutdown to close Kafka connection
def shutdown_producer():
    """Close the Kafka producer connection properly"""
    global producer
    if producer is not None:
        logger.info("Closing Kafka producer connection...")
        try:
            producer.flush()
            producer.close()
        except Exception as e:
            logger.error(f"Error closing Kafka producer: {str(e)}")
        logger.info("Kafka producer connection closed.")
        producer = None

if __name__ == "__main__":
    # Start watchdog thread
    watchdog_thread = threading.Thread(target=connection_watchdog, daemon=True)
    watchdog_thread.start()
    
    try:
        logger.info(f"Starting Kafka forwarder server on port {PORT}...")
        app.run(host='0.0.0.0', port=PORT, threaded=True)
    finally:
        shutdown_producer()
