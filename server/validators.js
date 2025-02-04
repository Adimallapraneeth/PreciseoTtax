const { body } = require('express-validator');

const appointmentValidators = [
    body('firstName')
        .trim()
        .notEmpty().withMessage('First name is required')
        .isLength({ min: 2, max: 50 }).withMessage('First name must be between 2 and 50 characters')
        .matches(/^[a-zA-Z\s.-]+$/).withMessage('First name can only contain letters, spaces, dots, and hyphens'),

    body('lastName')
        .trim()
        .notEmpty().withMessage('Last name is required')
        .isLength({ min: 2, max: 50 }).withMessage('Last name must be between 2 and 50 characters')
        .matches(/^[a-zA-Z\s.-]+$/).withMessage('Last name can only contain letters, spaces, dots, and hyphens'),

    body('email')
        .trim()
        .notEmpty().withMessage('Email is required')
        .isEmail().withMessage('Invalid email format')
        .normalizeEmail(),

    body('phone')
        .trim()
        .notEmpty().withMessage('Phone number is required')
        .matches(/^\d{10}$/).withMessage('Phone number must be 10 digits'),

    body('timeZone')
        .trim()
        .notEmpty().withMessage('Time zone is required')
        .isIn(['EST', 'PST']).withMessage('Invalid time zone'),

    body('preferredDate')
        .trim()
        .notEmpty().withMessage('Preferred date is required')
        .isISO8601().withMessage('Invalid date format')
        .custom(value => {
            const appointmentDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (appointmentDate < today) {
                throw new Error('Preferred date must be in the future');
            }
            return true;
        }),

    body('timeRange')
        .trim()
        .notEmpty().withMessage('Time range is required')
        .isIn(['early_morning', 'morning', 'afternoon', 'evening'])
        .withMessage('Invalid time range'),

    body('specificTime')
        .trim()
        .notEmpty().withMessage('Specific time is required'),

    body('message')
        .trim()
        .notEmpty().withMessage('Message is required')
        .isLength({ min: 10, max: 1000 }).withMessage('Message must be between 10 and 1000 characters')
];

module.exports = {
    appointmentValidators
};
