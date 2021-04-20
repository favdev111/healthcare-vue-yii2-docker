<?php
    $this->addSchemaOrg(
        'organization',
        'Organization',
        [
            '@context' => 'https://schema.org/',
            '@type' => 'Organization',
            'url' => Yii::$app->urlManager->createAbsoluteUrl('/'),
            'logo' => $this->theme->getAbsoluteUrl('img/logo-text.svg'),
            'legalName' => 'Hey Tutor LLC',
            'foundingLocation' => 'Los Angeles, CA',
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => '+1886699811',
                    'email' => 'info@winitclinic.com',
                    'contactType' => 'customer service',
                    'areaServed' => 'United States',
                    'sameAs' => [
                        'https://www.facebook.com/heytutor/',
                        'https://twitter.com/heytutor',
                        'https://www.instagram.com/heytutor/',
                        'https://www.linkedin.com/company/heytutor/about/'
                    ],
                ],
            ],
        ]
    );

    $query = \common\models\Review::getAllReviewsQuery();
    $query->cache(24 * 60 * 60);
    $reviewCount = $query->count();
    $ratingValue = 0;
    if ($reviewCount) {
        $ratingValue = round($query->sum('rating') / $reviewCount, 2);
    }

    $this->addSchemaOrg(
        'organization.aggregateRating',
        'AggregateRating',
        [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'reviewCount' => $reviewCount,
            'itemReviewed' => 'Tutors',
        ]
    );
?>
