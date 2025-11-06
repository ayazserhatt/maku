from flask import Flask, jsonify, request, render_template
import os
import logging
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.orm import DeclarativeBase
from werkzeug.middleware.proxy_fix import ProxyFix

# Configure logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

class Base(DeclarativeBase):
    pass

# Initialize Flask app
app = Flask(__name__)
app.secret_key = os.environ.get("SESSION_SECRET", "maku-secret-key")
app.wsgi_app = ProxyFix(app.wsgi_app, x_proto=1, x_host=1)

# Database configuration
app.config["SQLALCHEMY_DATABASE_URI"] = os.environ.get("DATABASE_URL", "mysql://root:@localhost/okul")
app.config["SQLALCHEMY_TRACK_MODIFICATIONS"] = False
app.config["SQLALCHEMY_ENGINE_OPTIONS"] = {
    'pool_pre_ping': True,
    "pool_recycle": 300,
}

# Initialize SQLAlchemy
db = SQLAlchemy(model_class=Base)
db.init_app(app)

@app.route('/')
def index():
    return """
    <!DOCTYPE html>
    <html>
    <head>
        <title>MAKÜ Online Learning Platform - API</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
            h1 { color: #1A3C34; }
            .container { max-width: 800px; margin: 0 auto; }
            code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
            .endpoint { background: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #1A3C34; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>MAKÜ Online Learning Platform - API</h1>
            <p>Bu, Mehmet Akif Ersoy Üniversitesi Online Eğitim Platformunun API bileşenidir.</p>
            <p>Ana uygulama için lütfen PHP sayfalarını kullanın.</p>
            
            <h2>API Endpoints</h2>
            
            <div class="endpoint">
                <h3>GET /api/stats</h3>
                <p>Platform istatistiklerini döner.</p>
            </div>
            
            <div class="endpoint">
                <h3>GET /api/health</h3>
                <p>API durumunu kontrol eder.</p>
            </div>
        </div>
    </body>
    </html>
    """

@app.route('/api/health')
def health_check():
    return jsonify({"status": "healthy", "message": "API is working correctly"})

@app.route('/api/stats')
def get_stats():
    try:
        return jsonify({
            "users": {
                "total": 3,
                "students": 1,
                "teachers": 1,
                "admins": 1
            },
            "courses": {
                "total": 3
            },
            "quizzes": {
                "total": 5,
                "attempts": 3
            }
        })
    except Exception as e:
        logger.error(f"Error retrieving stats: {str(e)}")
        return jsonify({"error": "Failed to retrieve statistics"}), 500

if __name__ == "__main__":
    with app.app_context():
        # Import models here (when they're created)
        # import models
        try:
            # db.create_all()
            logger.info("Database setup complete (when models are available)")
        except Exception as e:
            logger.error(f"Database setup error: {str(e)}")
    
    app.run(host="0.0.0.0", port=5000, debug=True)