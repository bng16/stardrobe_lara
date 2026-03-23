<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Critical Job Failure Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #dc2626;">⚠️ Critical Job Failure Alert</h1>
        
        <p>A critical background job has failed after all retry attempts.</p>
        
        <div style="background-color: #fee2e2; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #991b1b;">Job Details</h2>
            <p style="margin: 5px 0;"><strong>Job:</strong> {{ $jobName }}</p>
            <p style="margin: 5px 0;"><strong>Time:</strong> {{ now()->toDateTimeString() }}</p>
        </div>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Context</h3>
            @foreach($context as $key => $value)
                <p style="margin: 5px 0;"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
            @endforeach
        </div>
        
        <div style="background-color: #fef2f2; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #991b1b;">Error Message</h3>
            <p style="font-family: monospace; background-color: #fff; padding: 10px; border-radius: 3px;">{{ $errorMessage }}</p>
        </div>
        
        <div style="background-color: #f9fafb; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Stack Trace</h3>
            <pre style="font-family: monospace; font-size: 12px; background-color: #fff; padding: 10px; border-radius: 3px; overflow-x: auto; white-space: pre-wrap;">{{ $stackTrace }}</pre>
        </div>
        
        <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #92400e;">Action Required</h3>
            <p>Please investigate this failure immediately. This job is critical to system operations.</p>
            <ul>
                <li>Check the application logs for more details</li>
                <li>Verify database integrity</li>
                <li>Check external service status (Stripe, email, etc.)</li>
                <li>Consider manual intervention if necessary</li>
            </ul>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            This is an automated alert from the Creator Marketplace system.
        </p>
    </div>
</body>
</html>
