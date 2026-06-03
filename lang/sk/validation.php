<?php

return [
    'accepted'        => ':attribute musí byť akceptovaný.',
    'accepted_if'     => ':attribute musí byť akceptovaný, keď :other je :value.',
    'active_url'      => ':attribute nie je platná URL adresa.',
    'after'           => ':attribute musí byť dátum po :date.',
    'after_or_equal'  => ':attribute musí byť dátum po alebo rovný :date.',
    'alpha'           => ':attribute musí obsahovať iba písmená.',
    'alpha_dash'      => ':attribute musí obsahovať iba písmená, číslice, pomlčky a podčiarkovníky.',
    'alpha_num'       => ':attribute musí obsahovať iba písmená a číslice.',
    'array'           => ':attribute musí byť pole.',
    'before'          => ':attribute musí byť dátum pred :date.',
    'before_or_equal' => ':attribute musí byť dátum pred alebo rovný :date.',
    'between'         => [
        'array'   => ':attribute musí mať medzi :min a :max prvkami.',
        'file'    => ':attribute musí mať medzi :min a :max kilobajtmi.',
        'numeric' => ':attribute musí byť medzi :min a :max.',
        'string'  => ':attribute musí mať medzi :min a :max znakmi.',
    ],
    'boolean'           => 'Pole :attribute musí byť true alebo false.',
    'confirmed'         => 'Potvrdenie :attribute sa nezhoduje.',
    'current_password'  => 'Heslo je nesprávne.',
    'date'              => ':attribute nie je platný dátum.',
    'date_equals'       => ':attribute musí byť dátum rovnaký ako :date.',
    'date_format'       => ':attribute sa nezhoduje s formátom :format.',
    'declined'          => ':attribute musí byť odmietnuté.',
    'declined_if'       => ':attribute musí byť odmietnuté, keď :other je :value.',
    'different'         => ':attribute a :other musia byť odlišné.',
    'digits'            => ':attribute musí mať :digits číslic.',
    'digits_between'    => ':attribute musí mať medzi :min a :max číslicami.',
    'dimensions'        => ':attribute má neplatné rozmery obrázka.',
    'distinct'          => 'Pole :attribute má duplicitnú hodnotu.',
    'doesnt_start_with' => ':attribute nesmie začínať jedným z nasledujúcich: :values.',
    'email'             => ':attribute musí byť platná e-mailová adresa.',
    'ends_with'         => ':attribute musí končiť jedným z nasledujúcich: :values.',
    'enum'              => 'Vybraný :attribute je neplatný.',
    'exists'            => 'Vybraný :attribute je neplatný.',
    'file'              => ':attribute musí byť súbor.',
    'filled'            => 'Pole :attribute musí mať hodnotu.',
    'gt'                => [
        'array'   => ':attribute musí mať viac ako :value prvkov.',
        'file'    => ':attribute musí byť väčší než :value kilobajtov.',
        'numeric' => ':attribute musí byť väčší než :value.',
        'string'  => ':attribute musí mať viac ako :value znakov.',
    ],
    'gte' => [
        'array'   => ':attribute musí mať :value položiek alebo viac.',
        'file'    => ':attribute musí mať viac než alebo rovno :value kilobajtov.',
        'numeric' => ':attribute musí byť väčší než alebo rovno :value.',
        'string'  => ':attribute musí mať viac než alebo rovno :value znakov.',
    ],
    'image'    => ':attribute musí byť obrázok.',
    'in'       => 'Vybraný :attribute je neplatný.',
    'in_array' => 'Pole :attribute neexistuje v :other.',
    'integer'  => ':attribute musí byť celé číslo.',
    'ip'       => ':attribute musí byť platná IP adresa.',
    'ipv4'     => ':attribute musí byť platná IPv4 adresa.',
    'ipv6'     => ':attribute musí byť platná IPv6 adresa.',
    'json'     => ':attribute musí byť platný JSON reťazec.',
    'lt'       => [
        'array'   => ':attribute musí mať menej než :value položiek.',
        'file'    => ':attribute musí mať menej než :value kilobajtov.',
        'numeric' => ':attribute musí byť menšie než :value.',
        'string'  => ':attribute musí mať menej než :value znakov.',
    ],
    'lte' => [
        'array'   => ':attribute nesmie mať viac ako :value položiek.',
        'file'    => ':attribute musí mať menej alebo rovno :value kilobajtov.',
        'numeric' => ':attribute musí byť menšie alebo rovno :value.',
        'string'  => ':attribute musí mať menej alebo rovno :value znakov.',
    ],
    'mac_address' => ':attribute musí byť platná MAC adresa.',
    'max'         => [
        'array'   => ':attribute nesmie mať viac ako :max položiek.',
        'file'    => ':attribute nesmie mať viac ako :max kilobajtov.',
        'numeric' => ':attribute nesmie byť väčšie než :max.',
        'string'  => ':attribute nesmie mať viac ako :max znakov.',
    ],
    'mimes'     => ':attribute musí byť súbor typu: :values.',
    'mimetypes' => ':attribute musí byť súbor typu: :values.',
    'min'       => [
        'array'   => ':attribute musí mať aspoň :min položiek.',
        'file'    => ':attribute musí mať aspoň :min kilobajtov.',
        'numeric' => ':attribute musí byť aspoň :min.',
        'string'  => ':attribute musí mať aspoň :min znakov.',
    ],
    'multiple_of' => ':attribute musí byť násobkom čísla :value.',
    'not_in'      => 'Vybraný :attribute je neplatný.',
    'not_regex'   => 'Formát :attribute je neplatný.',
    'numeric'     => ':attribute musí byť číslo.',
    'password'    => [
        'letters'       => ':attribute musí obsahovať aspoň jedno písmeno.',
        'mixed'         => ':attribute musí obsahovať aspoň jedno veľké písmeno a jedno malé písmeno.',
        'numbers'       => ':attribute musí obsahovať aspoň jednu číslicu.',
        'symbols'       => ':attribute musí obsahovať aspoň jeden symbol.',
        'uncompromised' => 'Zadaný :attribute sa objavil v úniku údajov. Prosím, vyberte iný :attribute.',
    ],
    'present'              => 'Pole :attribute musí byť prítomné.',
    'prohibited'           => 'Pole :attribute je zakázané.',
    'prohibited_if'        => 'Pole :attribute je zakázané, keď :other je :value.',
    'prohibited_unless'    => 'Pole :attribute je zakázané, pokiaľ :other nie je v :values.',
    'prohibits'            => 'Pole :attribute zakazuje prítomnosť :other.',
    'regex'                => 'Formát :attribute je neplatný.',
    'required'             => 'Pole :attribute je povinné.',
    'required_array_keys'  => 'Pole :attribute musí obsahovať položky pre: :values.',
    'required_if'          => 'Pole :attribute je povinné, keď :other je :value.',
    'required_unless'      => 'Pole :attribute je povinné, pokiaľ :other nie je v :values.',
    'required_with'        => 'Pole :attribute je povinné, keď je prítomný :values.',
    'required_with_all'    => 'Pole :attribute je povinné, keď sú prítomné všetky :values.',
    'required_without'     => 'Pole :attribute je povinné, keď nie je prítomný :values.',
    'required_without_all' => 'Pole :attribute je povinné, keď nie je prítomný žiadny z :values.',
    'same'                 => ':attribute a :other sa musia zhodovať.',
    'size'                 => [
        'array'   => ':attribute musí obsahovať :size položiek.',
        'file'    => ':attribute musí mať veľkosť :size kilobajtov.',
        'numeric' => ':attribute musí mať veľkosť :size.',
        'string'  => ':attribute musí mať veľkosť :size znakov.',
    ],
    'starts_with' => ':attribute musí začínať jedným z nasledujúcich: :values.',
    'string'      => ':attribute musí byť reťazec.',
    'timezone'    => ':attribute musí byť platné časové pásmo.',
    'unique'      => ':attribute už bol obsadený.',
    'uploaded'    => ':attribute sa nepodarilo nahrať.',
    'url'         => ':attribute musí byť platná URL adresa.',
    'uuid'        => ':attribute musí byť platný UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'vlastná správa',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
];
