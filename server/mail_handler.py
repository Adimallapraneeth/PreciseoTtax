from flask import Flask, request, jsonify
from flask_cors import CORS
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import os
from datetime import datetime
import ssl

app = Flask(__name__)
# More permissive CORS settings for testing
CORS(app, resources={
    r"/api/*": {
        "origins": "*",  # Allow all origins for testing
        "methods": ["POST", "OPTIONS"],
        "allow_headers": ["Content-Type", "Accept"],
        "supports_credentials": True
    }
})

# Microsoft 365 Email configuration
SMTP_SERVER = "outlook.office365.com"
SMTP_PORT = 587
SENDER_EMAIL = "info@preciseotax.com"
SENDER_PASSWORD = "KhobTax@2024"
RECIPIENT_EMAIL = "info@preciseotax.com"

def send_email(subject, recipient, html_content):
    try:
        msg = MIMEMultipart('alternative')
        msg['Subject'] = subject
        msg['From'] = SENDER_EMAIL
        msg['To'] = recipient
        
        html_part = MIMEText(html_content, 'html')
        msg.attach(html_part)
        
        print(f"Attempting to connect to SMTP server: {SMTP_SERVER}:{SMTP_PORT}")
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            print("Connected to SMTP server")
            print("Starting TLS connection...")
            server.ehlo()
            server.starttls()
            server.ehlo()
            print("TLS connection established")
            print(f"Attempting to login with email: {SENDER_EMAIL}")
            server.login(SENDER_EMAIL, SENDER_PASSWORD)
            print("Login successful")
            print(f"Sending email to: {recipient}")
            server.sendmail(SENDER_EMAIL, recipient, msg.as_string())
            print(f"Email sent successfully to {recipient}")
        return True
    except smtplib.SMTPAuthenticationError as e:
        print(f"SMTP Authentication Error: {str(e)}")
        print("This usually means the username or password is incorrect")
        return False
    except smtplib.SMTPConnectError as e:
        print(f"SMTP Connection Error: {str(e)}")
        print("This usually means the SMTP server settings are incorrect")
        return False
    except smtplib.SMTPException as e:
        print(f"SMTP Error: {str(e)}")
        print(f"Error details: {str(e.__dict__)}")
        return False
    except Exception as e:
        print(f"Error sending email: {str(e)}")
        print(f"Error type: {type(e)}")
        return False

def get_admin_email_template(data):
    return f"""
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #0066cc;">New Consultation Request</h2>
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 5px 0;"><strong>Name:</strong> {data.get('firstName')} {data.get('lastName')}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {data.get('email')}</p>
            <p style="margin: 5px 0;"><strong>Phone:</strong> {data.get('phone')}</p>
            <p style="margin: 5px 0;"><strong>Time Zone:</strong> {data.get('timeZone')}</p>
            <p style="margin: 5px 0;"><strong>Preferred Date:</strong> {data.get('preferredDate')}</p>
            <p style="margin: 5px 0;"><strong>Time Range:</strong> {data.get('timeRange')}</p>
            <p style="margin: 5px 0;"><strong>Specific Time:</strong> {data.get('specificTime')}</p>
            <p style="margin: 5px 0;"><strong>Message:</strong> {data.get('message')}</p>
            <p style="margin: 5px 0;"><strong>Submitted:</strong> {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
        </div>
    </div>
    """

def get_client_email_template(data):
    return f"""
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #0066cc;">Thank you for your consultation request!</h2>
        <p>Dear {data.get('firstName')} {data.get('lastName')},</p>
        <p>We have received your consultation request for:</p>
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 5px 0;"><strong>Date:</strong> {data.get('preferredDate')}</p>
            <p style="margin: 5px 0;"><strong>Time Range:</strong> {data.get('timeRange')}</p>
            <p style="margin: 5px 0;"><strong>Specific Time:</strong> {data.get('specificTime')}</p>
            <p style="margin: 5px 0;"><strong>Time Zone:</strong> {data.get('timeZone')}</p>
        </div>
        <p>Our team will review your request and contact you shortly to confirm the consultation.</p>
        <p>If you need to make any changes or have questions, please contact us at:</p>
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 5px 0;"><strong>Phone:</strong> (551) 758-9773</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> info@preciseotax.com</p>
        </div>
        <p style="color: #666; font-size: 0.9em; margin-top: 20px;">
            This is an automated message. Please do not reply to this email.
        </p>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="margin: 5px 0;">Best regards,<br>Preciseo Tax Services Team</p>
        </div>
    </div>
    """

@app.route('/api/submit-appointment', methods=['POST'])
def submit_appointment():
    try:
        data = request.get_json()
        print("Received form data:", data)
        
        # Send email to admin
        print("Attempting to send admin email...")
        admin_html = get_admin_email_template(data)
        admin_success = send_email(
            "New Consultation Request - Preciseo Tax Services",
            RECIPIENT_EMAIL,
            admin_html
        )
        
        if not admin_success:
            print("Failed to send admin email")
            return jsonify({
                "success": False,
                "message": "Failed to send notification email. Please check server logs for details."
            }), 500
        
        # Send confirmation email to client
        print("Attempting to send client email...")
        client_html = get_client_email_template(data)
        client_success = send_email(
            "Thank you for your consultation request - Preciseo Tax Services",
            data.get('email'),
            client_html
        )
        
        if not client_success:
            print("Failed to send client email")
            return jsonify({
                "success": False,
                "message": "Failed to send confirmation email. Please check server logs for details."
            }), 500
            
        if admin_success and client_success:
            print("Both emails sent successfully")
            return jsonify({
                "success": True,
                "message": "Your consultation request has been sent successfully!"
            }), 200
            
    except Exception as e:
        print(f"Error processing request: {str(e)}")
        return jsonify({
            "success": False,
            "message": f"An error occurred while processing your request: {str(e)}"
        }), 500

if __name__ == '__main__':
    print("Server starting on http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=True)
