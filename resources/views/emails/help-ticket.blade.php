<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Help Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .priority {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .priority-low { background-color: #d4edda; color: #155724; }
        .priority-medium { background-color: #fff3cd; color: #856404; }
        .priority-high { background-color: #f8d7da; color: #721c24; }
        .priority-urgent { background-color: #721c24; color: #fff; }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #495057;
        }
        .field-value {
            margin-top: 5px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .footer {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Help Ticket Submitted</h1>
        <p>A new help ticket has been submitted through the website.</p>
    </div>

    <div class="content">
        <div class="field">
            <div class="field-label">Name:</div>
            <div class="field-value">{{ $name ?? 'Not provided' }}</div>
        </div>

        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">{{ $email ?? 'Not provided' }}</div>
        </div>

        <div class="field">
            <div class="field-label">Subject:</div>
            <div class="field-value">{{ $subject ?? 'No subject' }}</div>
        </div>

        <div class="field">
            <div class="field-label">Priority:</div>
            <div class="field-value">
                <span class="priority priority-{{ $priority ?? 'medium' }}">{{ $priority ?? 'medium' }}</span>
            </div>
        </div>
        <div class="field">
            <div class="field-label">Message:</div>
            <div class="field-value" style="white-space: pre-wrap;">{{ $messageText ?? 'No message provided' }}</div>
        </div>
    </div>
</body>
</html>
