<?php

it('allows guests to switch locale and persists via session and cookie', function () {
    $response = $this->post('/locale', ['locale' => 'ar']);

    $response->assertSessionHas('locale', 'ar');
    $response->assertCookie('tradeon_locale', 'ar');
});

it('rejects unsupported locales', function () {
    $this->post('/locale', ['locale' => 'fr'])->assertSessionMissing('locale');
});

it('has matching translation keys between en and ar', function () {
    $en = json_decode(file_get_contents(lang_path('en.json')), true);
    $ar = json_decode(file_get_contents(lang_path('ar.json')), true);

    $flatten = function (array $arr, string $prefix = '') use (&$flatten) {
        $keys = [];
        foreach ($arr as $k => $v) {
            $full = $prefix === '' ? $k : $prefix.'.'.$k;
            if (is_array($v)) {
                $keys = array_merge($keys, $flatten($v, $full));
            } else {
                $keys[] = $full;
            }
        }

        return $keys;
    };

    $enKeys = $flatten($en);
    $arKeys = $flatten($ar);

    $missing = array_diff($enKeys, $arKeys);

    expect($missing)->toBeEmpty('Missing Arabic translations: '.implode(', ', $missing));
});
