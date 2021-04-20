<?php
    $this->params['title'] = 'Congratulations';
    $blocks = [
        [
            'text' => 'Your account has been activated!',
            'padding' => '10px 0',
            'fontWeight' => '700',
        ],
        'Now that you’re live on the marketplace, patients are able to view your profile and send consultation requests. Be sure to set your availability, add any missing details to your bio, and select every area of expertise. The more you add, the greater your success.',
        [
            'text' => 'Reminders',
            'padding' => '10px 0',
            'fontWeight' => '700',
        ],
        [
            'text' => '1. Profile ranking',
            'textAlign' => 'left',
            'padding' => '10px 15px',
            'fontWeight' => '700',
        ],
        [
            'text' => 'This determines your profile visibility when patients search. The higher your ranking, the more likely you are to receive consultation requests. Your response time, duration of consultations logged, and ratings/reviews are paramount. You may transfer any personal reviews you have from previous patients to help increase your ranking.',
            'textAlign' => 'left',
            'padding' => '0 15px 10px',
        ],
        [
            'text' => '2. Consultations',
            'textAlign' => 'left',
            'padding' => '10px 15px',
            'fontWeight' => '700',
        ],
        [
            'text' => 'Patients will only be able to submit consultation requests for the availability you select on your dashboard. You may accept, decline, or revise the times. Statistics show that providers who respond to requests quickly are more likely to develop longer lasting relationships with their patients. Be sure to enter detailed notes for them after your consultations as well to keep them coming back!',
            'textAlign' => 'left',
            'padding' => '0 15px 10px',
        ],
        [
            'text' => '3. Payments',
            'textAlign' => 'left',
            'padding' => '10px 15px',
            'fontWeight' => '700',
        ],
        [
            'text' => 'Set up your direct deposit by logging into your account and entering your information under “payouts.” You’ll be paid every 3 business days after each consultation, and you’ll also receive commission on purchases made for anything you input under “recommendations” after your consultations. All payments are made via direct deposit.',
            'textAlign' => 'left',
            'padding' => '0 15px 10px',
        ],
        'If you have any questions, please feel free to reach us at <a href="mailto:support@winitclinic.com" style="display: inline-block; text-decoration: underline; color:#7A27C5;">support@winitclinic.com</a>.',
        'Thank you for making us healthy!',
    ];

    foreach ($blocks as $block) {
        if (is_array($block)) {
            $data = $block;
        } else {
            $data = [
                'text' => $block,
                'padding' => '10px 0',
                'textAlign' => 'left',
            ];
        }

        echo $this->render(
            '../_parts/common-text',
            $data
        );
    }



