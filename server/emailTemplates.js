const getAdminEmailTemplate = (data) => `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #0066cc;">New Consultation Request</h2>
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 5px 0;"><strong>Name:</strong> ${data.firstName} ${data.lastName}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> ${data.email}</p>
            <p style="margin: 5px 0;"><strong>Phone:</strong> ${data.phone}</p>
            <p style="margin: 5px 0;"><strong>Time Zone:</strong> ${data.timeZone}</p>
            <p style="margin: 5px 0;"><strong>Preferred Date:</strong> ${data.preferredDate}</p>
            <p style="margin: 5px 0;"><strong>Time Range:</strong> ${data.timeRange}</p>
            <p style="margin: 5px 0;"><strong>Specific Time:</strong> ${data.specificTime}</p>
            <p style="margin: 5px 0;"><strong>Message:</strong> ${data.message}</p>
            <p style="margin: 5px 0;"><strong>Submitted:</strong> ${new Date().toLocaleString()}</p>
        </div>
    </div>
`;

const getClientEmailTemplate = (data) => `
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #0066cc;">Thank you for your consultation request!</h2>
        <p>Dear ${data.firstName} ${data.lastName},</p>
        <p>We have received your consultation request for:</p>
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 5px 0;"><strong>Date:</strong> ${data.preferredDate}</p>
            <p style="margin: 5px 0;"><strong>Time Range:</strong> ${data.timeRange}</p>
            <p style="margin: 5px 0;"><strong>Specific Time:</strong> ${data.specificTime}</p>
            <p style="margin: 5px 0;"><strong>Time Zone:</strong> ${data.timeZone}</p>
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
`;

module.exports = {
    getAdminEmailTemplate,
    getClientEmailTemplate
};
