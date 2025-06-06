# Kafka Forwarder Installation Guide

This document describes how to install and run the Kafka Forwarder as a systemd daemon on Ubuntu.

## Overview

The Kafka Forwarder is a Python service that receives HTTP POST requests and forwards them to Kafka. It acts as a bridge between Laravel PHP applications and Kafka, maintaining a persistent connection to reduce overhead.

## Prerequisites

- Ubuntu 18.04 or later
- Python 3.8 or later
- pip (Python package manager)
- Access to Kafka broker(s)
- SSL certificates for Kafka (kafka-service.cert and kafka-service.key)

## Local Development and Testing

For local development or testing, you can run the service directly:

```bash
# Install required packages
pip install flask kafka-python python-dotenv

# Create a .env file in the same directory as the script
# with your Kafka configuration:
#   KAFKA_BROKERS=your-broker:9092
#   KAFKA_TOPIC=your-topic
#   KAFKA_CERT_PATH=/path/to/kafka-service.cert
#   KAFKA_KEY_PATH=/path/to/kafka-service.key

# Run the script
python kafka_forwarder.py
```

This will start the service on port 6666 (default) and connect to your Kafka server. All logs will be printed to the console and also saved in the `logs` directory.

## Installation Steps

### 1. Install Required Packages

```bash
sudo apt update
sudo apt install -y python3 python3-pip python3-venv
```

### 2. Create a Service User

```bash
sudo useradd -r -s /bin/false kafka_forwarder
```

### 3. Create Directory Structure

```bash
sudo mkdir -p /opt/kafka_forwarder
sudo mkdir -p /opt/kafka_forwarder/logs
sudo mkdir -p /etc/kafka_forwarder
```

### 4. Set Up Virtual Environment

```bash
cd /opt/kafka_forwarder
sudo python3 -m venv venv
sudo /opt/kafka_forwarder/venv/bin/pip install flask kafka-python python-dotenv
```

### 5. Copy the Application Files

Copy the `kafka_forwarder.py` script to the application directory:

```bash
sudo cp /path/to/your/kafka_forwarder.py /opt/kafka_forwarder/
```

### 6. Set Up SSL Certificates

Copy your Kafka SSL certificates to the configuration directory:

```bash
sudo cp /path/to/your/kafka-service.cert /etc/kafka_forwarder/
sudo cp /path/to/your/kafka-service.key /etc/kafka_forwarder/
```

### 7. Create Environment Configuration

Create a `.env` file with your configuration:

```bash
sudo nano /opt/kafka_forwarder/.env
```

Add the following content (adjust as needed):

```
FORWARDER_PORT=6666
KAFKA_BROKERS=kafka-broker1:9092,kafka-broker2:9092
KAFKA_TOPIC=your_topic_name
KAFKA_CERT_PATH=/etc/kafka_forwarder/kafka-service.cert
KAFKA_KEY_PATH=/etc/kafka_forwarder/kafka-service.key
KAFKA_TIMEOUT=10
```

### 8. Set Permissions

```bash
sudo chown -R kafka_forwarder:kafka_forwarder /opt/kafka_forwarder
sudo chown -R kafka_forwarder:kafka_forwarder /etc/kafka_forwarder
sudo chmod 600 /etc/kafka_forwarder/kafka-service.*
```

## Setting Up the Systemd Service

### 1. Create a Systemd Service File

```bash
sudo nano /etc/systemd/system/kafka-forwarder.service
```

Add the following content:

```ini
[Unit]
Description=Kafka Forwarder Service
After=network.target

[Service]
User=kafka_forwarder
Group=kafka_forwarder
WorkingDirectory=/opt/kafka_forwarder
ExecStart=/opt/kafka_forwarder/venv/bin/python /opt/kafka_forwarder/kafka_forwarder.py
Restart=always
RestartSec=5
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=kafka-forwarder
Environment=PYTHONUNBUFFERED=1

[Install]
WantedBy=multi-user.target
```

### 2. Enable and Start the Service

```bash
sudo systemctl daemon-reload
sudo systemctl enable kafka-forwarder
sudo systemctl start kafka-forwarder
```

### 3. Check Service Status

```bash
sudo systemctl status kafka-forwarder
```

## Monitoring and Troubleshooting

### View Logs

View systemd service logs:

```bash
sudo journalctl -u kafka-forwarder -f
```

View application logs:

```bash
sudo tail -f /opt/kafka_forwarder/logs/kafka_forwarder.log
```

### Health Check

The service provides a health endpoint. You can check its status with:

```bash
curl http://localhost:6666/health
```

### Restarting the Service

After configuration changes:

```bash
sudo systemctl restart kafka-forwarder
```

## Security Considerations

- Consider adding firewall rules to restrict access to port 6666
- Ensure SSL certificates have appropriate permissions
- Consider implementing authentication for the HTTP endpoint if needed

## Performance Tuning

For high-volume environments, you may want to:

1. Increase the number of worker threads in the Flask configuration
2. Monitor memory usage and adjust resource limits as needed
3. Consider using a production WSGI server like Gunicorn

For extremely high throughput, consider replacing Flask with a more performant async framework like FastAPI