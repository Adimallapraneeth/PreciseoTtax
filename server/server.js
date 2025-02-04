const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const { validationResult } = require('express-validator');
const compression = require('compression');
const xss = require('xss');
const path = require('path');

const config = require('./config');
const logger = require('./logger');
const { appointmentValidators } = require('./validators');
const { getAdminEmailTemplate, getClientEmailTemplate } = require('./emailTemplates');

const app = express();

// Security middleware
app.use(helmet());
app.use(cors(config.cors));
app.use(compression());

// Rate limiting
const limiter = rateLimit(config.rateLimit);
app.use('/api/', limiter);

// Body parsing middleware
app.use(express.json());

// Email configuration
const transporter = nodemailer.createTransport({
    host: "smtpout.secureserver.net",
    port: 465,
    secure: true, // Use SSL/TLS
    auth: {
        user: config.email.user,
        pass: config.email.pass
    }
});

// Verify email configuration on startup
transporter.verify()
    .then(() => {
        logger.info('Email service is ready');
        logger.info('Using email account:', config.email.user);
    })
    .catch(err => {
        logger.error('Email service verification failed:', {
            error: err.message,
            stack: err.stack,
            code: err.code,
            command: err.command
        });
    });

// Sanitize input
const sanitizeInput = (data) => {
    const sanitized = {};
    for (const [key, value] of Object.entries(data)) {
        sanitized[key] = typeof value === 'string' ? xss(value.trim()) : value;
    }
    return sanitized;
};

// Handle appointment form submissions
app.post('/api/submit-appointment', appointmentValidators, async (req, res) => {
    try {
        // Log incoming request
        logger.info('Received appointment request:', {
            email: req.body.email,
            date: req.body.date,
            time: req.body.time
        });

        // Validation check
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            logger.warn('Validation failed', { 
                errors: errors.array(),
                body: req.body 
            });
            return res.status(400).json({ 
                success: false,
                message: 'Validation failed',
                errors: errors.array()
            });
        }

        // Sanitize input
        const sanitizedData = sanitizeInput(req.body);
        
        // Log sanitized data
        logger.info('Sanitized appointment data:', sanitizedData);

        // Prepare email options
        const adminMailOptions = {
            from: config.email.user,
            to: 'info@preciseotax.com', 
            subject: 'New Appointment Request - Preciseo Tax Services',
            html: getAdminEmailTemplate(sanitizedData)
        };

        const clientMailOptions = {
            from: config.email.user,
            to: sanitizedData.email,
            subject: 'Appointment Request Confirmation - Preciseo Tax Services',
            html: getClientEmailTemplate(sanitizedData)
        };

        logger.info('Attempting to send emails...');

        // Send emails
        try {
            await Promise.all([
                transporter.sendMail(adminMailOptions),
                transporter.sendMail(clientMailOptions)
            ]);

            logger.info('Emails sent successfully', {
                admin: adminMailOptions.to,
                client: clientMailOptions.to
            });

            res.status(200).json({
                success: true,
                message: 'Your appointment request has been received. We will contact you shortly to confirm.'
            });
        } catch (emailError) {
            logger.error('Failed to send emails:', {
                error: emailError.message,
                stack: emailError.stack,
                code: emailError.code,
                command: emailError.command
            });
            throw emailError;
        }

        logger.info('Appointment request processed successfully', {
            email: sanitizedData.email,
            date: sanitizedData.date,
            time: sanitizedData.time
        });
    } catch (error) {
        logger.error('Error processing appointment request:', {
            error: error.message,
            stack: error.stack,
            code: error.code,
            command: error.command
        });
        res.status(500).json({ 
            success: false,
            message: 'Error processing your request. Please try again later.' 
        });
    }
});

// Error handling middleware
app.use((err, req, res, next) => {
    logger.error('Unhandled error:', err);
    res.status(500).json({ 
        success: false,
        message: 'An unexpected error occurred' 
    });
});

// Create logs directory if it doesn't exist
const logsDir = path.join(__dirname, 'logs');
require('fs').mkdirSync(logsDir, { recursive: true });

// Start server
const PORT = config.server.port;
app.listen(PORT, () => {
    logger.info(`Server running on port ${PORT} in ${config.server.env} mode`);
});
