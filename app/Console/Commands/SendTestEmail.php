<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email? : Email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Enter the email address to send test email to');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Sending test email to: ' . $email);
        $this->info('Please wait...');

        try {
            Mail::to($email)->send(new TestMail());

            $this->newLine();
            $this->info('✅ Test email sent successfully!');
            $this->info('Please check your inbox (and spam folder).');
            $this->newLine();

            // Display configuration info
            $this->table(
                ['Configuration', 'Value'],
                [
                    ['MAIL_MAILER', config('mail.default')],
                    ['MAIL_HOST', config('mail.mailers.smtp.host')],
                    ['MAIL_PORT', config('mail.mailers.smtp.port')],
                    ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                    ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                    ['MAIL_FROM_NAME', config('mail.from.name')],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Please check your email configuration in .env file:');
            $this->warn('- MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD');
            $this->warn('- Make sure you are using Gmail App Password, not regular password');
            $this->warn('- Check if 2-factor authentication is enabled on Gmail');

            return 1;
        }
    }
}
