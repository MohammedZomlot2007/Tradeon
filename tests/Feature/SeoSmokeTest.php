<?php

it('serves a valid sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');
    expect($response->getContent())->toContain('<urlset');
});

it('has matching taxonomy keys between en and ar', function () {
    $en = json_decode(file_get_contents(lang_path('en.json')), true);
    $ar = json_decode(file_get_contents(lang_path('ar.json')), true);

    foreach (['taxonomy.business_types', 'taxonomy.industries', 'taxonomy.countries'] as $group) {
        [$root, $sub] = explode('.', $group);
        expect($ar[$root][$sub] ?? null)->not->toBeNull("Missing group {$group} in ar.json");

        $missing = array_diff_key($en[$root][$sub], $ar[$root][$sub]);
        expect($missing)->toBeEmpty('Missing taxonomy keys in ar.json: '.implode(', ', array_keys($missing)));
    }
});

it('has flash translations for both locales', function () {
    $en = include lang_path('en/flash.php');
    $ar = include lang_path('ar/flash.php');

    $missing = array_diff_key($en, $ar);
    expect($missing)->toBeEmpty('Missing flash keys in ar/flash.php: '.implode(', ', array_keys($missing)));
});
