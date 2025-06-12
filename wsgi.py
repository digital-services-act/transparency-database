from kafka_forwarder import app, init_app

# Initialize the application
init_app()

# This is the WSGI entry point
if __name__ == "__main__":
    app.run()
