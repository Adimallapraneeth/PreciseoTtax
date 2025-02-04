require('dotenv').config();

module.exports = {
    email: {
        user: process.env.EMAIL_USER,
        pass: process.env.EMAIL_PASS,
        recipient: process.env.RECIPIENT_EMAIL || 'info@preciseotax.com'
    },
    server: {
        port: process.env.PORT || 3000,
        env: process.env.NODE_ENV || 'development'
    },
    rateLimit: {
        windowMs: 15 * 60 * 1000, // 15 minutes
        max: 5 // limit each IP to 5 requests per windowMs
    },
    cors: {
        origin: process.env.NODE_ENV === 'production' 
            ? ['https://adimallapraneeth.github.io']
            : ['http://localhost:3000', 'http://127.0.0.1:3000', 'http://localhost:8080'],
        methods: ['POST', 'OPTIONS'],
        allowedHeaders: ['Content-Type'],
        credentials: true
    },
    businessHours: {
        start: 9, // 9 AM
        end: 17   // 5 PM
    }
};
