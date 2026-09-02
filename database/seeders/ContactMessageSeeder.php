<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Kamrul Islam',
                'email' => 'kamrul.islam@example.com',
                'phone' => '+880 1712-556677',
                'subject' => 'Corporate sponsorship for the winter campaign',
                'message' => 'I work in CSR at a garments group in Khulna. We have a budget line for winter relief this year and would like to discuss sponsoring a portion of your blanket distribution. Could someone call me to arrange a meeting?',
                'is_read' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'name' => 'Nasrin Akter',
                'email' => 'nasrin.akter@example.com',
                'phone' => '+880 1819-334455',
                'subject' => 'How do I record a cash donation?',
                'message' => 'I gave Tk 5,000 in cash at your desk during the book fair last week but I do not have a transaction ID. How should I record it on the website so it shows in my history?',
                'is_read' => false,
                'created_at' => now()->subDays(2),
            ],
            [
                'name' => 'Dr. Selim Reza',
                'email' => 'selim.reza@example.com',
                'phone' => '+880 1911-887766',
                'subject' => 'Volunteering as a physician at the medical camps',
                'message' => 'I am a general physician practising in Khulna and would like to volunteer at your quarterly camps. I can commit to one Friday per quarter. Please let me know the process.',
                'is_read' => false,
                'created_at' => now()->subDays(4),
            ],
            [
                'name' => 'Farhan Kabir',
                'email' => 'farhan.kabir@example.com',
                'phone' => null,
                'subject' => 'Request for the annual expenditure report',
                'message' => 'Before I set up a monthly standing order I would like to read your most recent expenditure breakdown. Is a copy available?',
                'is_read' => true,
                'read_at' => now()->subDays(5),
                'created_at' => now()->subDays(6),
            ],
            [
                'name' => 'Rumana Haque',
                'email' => 'rumana.haque@example.com',
                'phone' => '+880 1611-223344',
                'subject' => 'Case referral — family in Dumuria',
                'message' => 'There is a family in my village whose house was damaged and the father is unable to work after an accident. Can I refer them for assessment? I can provide the union parishad contact.',
                'is_read' => true,
                'read_at' => now()->subDays(9),
                'created_at' => now()->subDays(10),
            ],
            [
                'name' => 'Imtiaz Ahmed',
                'email' => 'imtiaz.ahmed@example.com',
                'phone' => '+880 1521-778899',
                'subject' => 'Thank you from a beneficiary family',
                'message' => 'My mother received a food parcel every month for the past year. I am now working in Dhaka and would like to give back. Please add me to your donor list.',
                'is_read' => true,
                'read_at' => now()->subDays(14),
                'created_at' => now()->subDays(15),
            ],
        ];

        foreach ($messages as $data) {
            $isRead = $data['is_read'];
            $readAt = $data['read_at'] ?? null;
            $createdAt = $data['created_at'];
            unset($data['is_read'], $data['read_at'], $data['created_at']);

            $message = ContactMessage::updateOrCreate(
                ['email' => $data['email'], 'subject' => $data['subject']],
                $data + ['ip_address' => '127.0.0.1']
            );

            $message->forceFill([
                'is_read' => $isRead,
                'read_at' => $readAt,
                'created_at' => $createdAt,
            ])->save();
        }
    }
}
