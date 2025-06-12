# Kafka Forwarder Service

The Kafka Forwarder is a lightweight HTTP service that accepts incoming HTTP requests and forwards them to a Kafka topic. It's designed to be reliable, secure, and production-ready.

## Setup Instructions for Ubuntu Server

This guide explains how to set up the Kafka Forwarder service on an Ubuntu server using Gunicorn WSGI server for production deployment.

### Prerequisites

- Ubuntu Server (18.04 LTS or newer)
- Python 3.6+ 
- Kafka broker(s) accessible from the server

### Installation Steps

1. **Update your system**

   ```bash
   sudo apt update
   sudo apt upgrade -y
   ```

2. **Install required system packages**

   ```bash
   sudo apt install -y python3 python3-pip python3-dev python3-venv
   ```

3. **Create a dedicated user for the service (optional but recommended)**

   ```bash
   sudo useradd -m -s /bin/bash kafka_forwarder
   sudo su kafka_forwarder
   cd /home/kafka_forwarder
   ```

4. **Clone the repository**

   ```bash
   git clone https://github.com/your-repo/transparency-database.git
   cd transparency-database
   ```

5. **Create and activate a virtual environment**

   ```bash
   python3 -m venv venv
   source venv/bin/activate
   ```

6. **Install required Python packages**

   ```bash
   pip install -U pip
   pip install flask kafka-python python-dotenv gunicorn
   ```
   
   Or if you have a requirements.txt file:
   
   ```bash
   pip install -r requirements.txt
   ```

7. **Configure the environment**

   Create a `.env` file:

   ```bash
   touch .env
   ```

   Add the following configurations to the `.env` file (modify as needed):

   ```
   KAFKA_FORWARDER_PORT=6666
   KAFKA_BROKERS=kafka1.example.com:9092,kafka2.example.com:9092
   KAFKA_TOPIC=transparency_statements
   KAFKA_CERT_PATH=/path/to/kafka-service.cert
   KAFKA_KEY_PATH=/path/to/kafka-service.key
   KAFKA_TIMEOUT=10
   GUNICORN_WORKERS=4
   ```

8. **Set up Kafka SSL certificates**

   Place your Kafka certificates in the appropriate location:
   
   ```bash
   mkdir -p certs
   # Copy your certificate files to this directory
   # cp /path/to/source/kafka-service.cert certs/
   # cp /path/to/source/kafka-service.key certs/
   ```

9. **Create necessary directories**

   ```bash
   mkdir -p storage/logs
   ```

10. **Make the startup script executable**

    ```bash
    chmod +x start_server.sh
    ```

### Setting Up as a Systemd Service

1. **Create a systemd service file**

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
   WorkingDirectory=/home/kafka_forwarder/transparency-database
   ExecStart=/home/kafka_forwarder/transparency-database/venv/bin/gunicorn --bind 127.0.0.1:6666 --workers 4 wsgi:app
   Restart=always
   RestartSec=10

   # Optional: Configure resource limits
   # LimitNOFILE=65535

   Environment="PATH=/home/kafka_forwarder/transparency-database/venv/bin"
   EnvironmentFile=/home/kafka_forwarder/transparency-database/.env

   [Install]
   WantedBy=multi-user.target
   ```

2. **Enable and start the service**

   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable kafka-forwarder
   sudo systemctl start kafka-forwarder
   ```

3. **Check service status**

   ```bash
   sudo systemctl status kafka-forwarder
   ```

### Setting Up Nginx as a Reverse Proxy (Optional)

1. **Install Nginx**

   ```bash
   sudo apt install -y nginx
   ```

2. **Create a new site configuration**

   ```bash
   sudo nano /etc/nginx/sites-available/kafka-forwarder
   ```

   Add the following configuration:

   ```nginx
   server {
       listen 80;
       server_name yourserver.example.com;

       access_log /var/log/nginx/kafka-forwarder-access.log;
       error_log /var/log/nginx/kafka-forwarder-error.log;

       location / {
           proxy_pass http://127.0.0.1:6666;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       }
   }
   ```

3. **Enable the site**

   ```bash
   sudo ln -s /etc/nginx/sites-available/kafka-forwarder /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

4. **Set up SSL with Certbot (recommended)**

   ```bash
   sudo apt install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d yourserver.example.com
   ```

## Managing the Service

### Checking logs

```bash
# Application logs
tail -f /home/kafka_forwarder/transparency-database/storage/logs/kafka_forwarder.log
# Gunicorn logs
tail -f /home/kafka_forwarder/transparency-database/storage/logs/gunicorn-*.log
# Systemd service logs
sudo journalctl -u kafka-forwarder -f
```

### Starting and stopping the service

```bash
sudo systemctl start kafka-forwarder
sudo systemctl stop kafka-forwarder
sudo systemctl restart kafka-forwarder
```

### Checking service health

```bash
curl http://localhost:6666/health
```

## How It Works

This service has been converted from a Flask development server to a production-ready deployment using Gunicorn WSGI server. The key components include:

1. **wsgi.py**: The WSGI entry point for the application
2. **kafka_forwarder.py**: The main application code
3. **start_server.sh**: A helper script to start the Gunicorn server
4. **Systemd service**: For automatic startup and management

The WSGI server (Gunicorn) offers several advantages over the Flask development server:
- Improved performance and concurrency with multiple worker processes
- Better resource usage and stability
- Proper handling of signals and graceful shutdowns
- Production-grade error handling and logging

## API Endpoints

- `POST /send` - Send a message to Kafka
- `GET /health` - Health check endpoint