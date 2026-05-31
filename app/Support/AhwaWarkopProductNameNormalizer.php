<?php

namespace App\Support;

class AhwaWarkopProductNameNormalizer
{
    public static function normalize(string $name): string
    {
        $clean = trim($name);
        $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s*-\s*/', '-', $clean) ?? $clean;
        $clean = trim($clean, " \t\n\r\0\x0B,");

        return self::aliasMap()[self::catalogKey($clean)] ?? $clean;
    }

    public static function catalogKey(string $name): string
    {
        $clean = trim($name);
        $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s*-\s*/', '-', $clean) ?? $clean;

        return mb_strtolower(trim($clean, " \t\n\r\0\x0B,"));
    }

    /**
     * @return array<string, string>
     */
    protected static function aliasMap(): array
    {
        return [
            'bakso' => 'BAKSO',
            'bluberry yoghurt' => 'Bluberry Yoghurt',
            'choco melt' => 'CHOCO MELT',
            'choco melt stik' => 'CHOCO MELT STIK',
            'chocolate crispy' => 'CHOCOLATE CRISPY',
            'coffee crispy stick' => 'COFFEE CRISPY STICK',
            'crispy balls' => 'CRISPY BALLS',
            'coffee caramel' => 'Coffee Caramel',
            'coffee vanila' => 'Coffee Vanila',
            'coklat' => 'Coklat',
            'fruit tea appel 500 ml' => 'FRUIT TEA APPEL 500 ML',
            'fruit tea blackcurrant 500 ml' => 'FRUIT TEA BLACKCURRANT 500 ML',
            'fruit tea freezee 500 ml' => 'FRUIT TEA FREEZEE 500 ML',
            'fruit tea guava 500 ml' => 'FRUIT TEA GUAVA 500 ML',
            'fruit tea strawberry 500 ml' => 'FRUIT TEA STRAWBERRY 500 ML',
            'fruizzy grape' => 'Fruizzy Grape',
            'indomie goreng' => 'INDOMIE GORENG',
            'indomie kuah' => 'INDOMIE KUAH',
            'kari ayam' => 'KARI AYAM',
            'kentang goreng' => 'KENTANG GORENG',
            'kopi ahwa' => 'Kopi Ahwa',
            'kopi gula aren' => 'Kopi Gula Aren',
            'kopi susu' => 'Kopi Susu',
            'lemon tea' => 'Lemon Tea',
            'mie bangladesh' => 'MIE BANGLADESH',
            'miki-miki doubel choco' => 'MIKI-MIKI DOUBEL CHOCO',
            'miki-miki vanilla' => 'MIKI-MIKI VANILLA',
            'mix platter' => 'MIX PLATTER',
            'mochi chocolate' => 'MOCHI CHOCOLATE',
            'mochi vanilla' => 'MOCHI VANILLA',
            'mango sluch hi-c' => 'Mango Sluch HI-C',
            'matcha' => 'Matcha',
            'mienas' => 'MieNas',
            'mochi durian' => 'Mochi Durian',
            'nanas' => 'NANAS',
            'nugget' => 'NUGGET',
            'nasi goreng ahwa' => 'Nasi Goreng Ahwa',
            'otak-otak' => 'OTAK-OTAK',
            'pedes dower' => 'PEDES DOWER',
            'pedes gledek' => 'PEDES GLEDEK',
            'pop mie soto ayam' => 'POP MIE SOTO AYAM',
            'prima 1500ml' => 'PRIMA 1500ML',
            'prima 600 ml' => 'PRIMA 600 ML',
            'red velvet' => 'Red Velvet',
            'rempeyek kacang / teri' => 'Rempeyek Kacang / Teri',
            'semangka' => 'SEMANGKA',
            'sosis goreng' => 'SOSIS GORENG',
            'strawberry cone' => 'STRAWBERRY CONE',
            'strawberry crispy' => 'STRAWBERRY CRISPY',
            'sundae chocolate cup' => 'SUNDAE CHOCOLATE CUP',
            'sundae strawbery cup' => 'SUNDAE STRAWBERY CUP',
            'sweet corn' => 'SWEET CORN',
            'taro crispy' => 'TARO CRISPY',
            'tebs sparkling 500 ml' => 'TEBS SPARKLING 500 ML',
            'teh botol sosro' => 'TEH BOTOL SOSRO',
            'taro' => 'Taro',
            'teh tanjak' => 'Teh Tanjak',
            'thai tea' => 'Thai Tea',
            'ubi goreng' => 'UBI GORENG',
            'wortel kuda' => 'WORTEL KUDA',
        ];
    }
}
