<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Creator Account Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Welcome to Creator Marketplace!</h1>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>You've been invited to join our Creator Marketplace as a creator. Your account has been created and you can now log in to set up your shop.</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Temporary Password:</strong> {{ $temporaryPassword }}</p>
        </div>
        
        <p><strong>Important:</strong> Please change your password after your first login for security purposes.</p>
        
        <p>To get started:</p>
        <ol>
            <li>Log in using the credentials above</li>
            <li>Complete your shop onboarding</li>
            <li>Start listing your products</li>
        </ol>
        
        <p>If you have any questions, please contact our support team.</p>
        
        <p>Best regards,<br>The Creator Marketplace Team</p>
    </div>
</body>
</html>
