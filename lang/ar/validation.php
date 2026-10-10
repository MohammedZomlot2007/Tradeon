<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'string' => 'يجب أن يكون :attribute نصاً.',
    'max' => [
        'string' => 'يجب ألا يزيد :attribute عن :max حرفاً.',
        'file' => 'يجب ألا يزيد حجم :attribute عن :max كيلوبايت.',
    ],
    'min' => [
        'string' => 'يجب ألا يقل :attribute عن :min أحرف.',
    ],
    'email' => 'يجب أن يكون :attribute بريداً إلكترونياً صحيحاً.',
    'unique' => 'قيمة :attribute مستخدمة من قبل.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'in' => ':attribute المحدد غير صالح.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'mimes' => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'attributes' => [
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'name' => 'الاسم',
        'company_name' => 'اسم الشركة',
        'country' => 'البلد',
        'city' => 'المدينة',
        'industry' => 'الصناعة',
        'locale' => 'اللغة',
        'comment' => 'التعليق',
        'body' => 'الرسالة',
        'title' => 'العنوان',
        'content' => 'المحتوى',
        'document' => 'المستند',
        'document_type' => 'نوع المستند',
    ],
];
