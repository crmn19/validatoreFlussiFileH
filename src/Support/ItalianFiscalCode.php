<?php

namespace FlussiH\Validator\Support;

final class ItalianFiscalCode
{
    private const HOMOCODIA = ['L'=>'0','M'=>'1','N'=>'2','P'=>'3','Q'=>'4','R'=>'5','S'=>'6','T'=>'7','U'=>'8','V'=>'9'];

    public static function isValid(string $value): bool
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^(STP|ENI)[A-Z0-9]{13}$/', $value) === 1) {
            return true;
        }

        if (preg_match('/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/', $value) !== 1) {
            return false;
        }

        $odd = [
            '0'=>1,'1'=>0,'2'=>5,'3'=>7,'4'=>9,'5'=>13,'6'=>15,'7'=>17,'8'=>19,'9'=>21,
            'A'=>1,'B'=>0,'C'=>5,'D'=>7,'E'=>9,'F'=>13,'G'=>15,'H'=>17,'I'=>19,'J'=>21,
            'K'=>2,'L'=>4,'M'=>18,'N'=>20,'O'=>11,'P'=>3,'Q'=>6,'R'=>8,'S'=>12,'T'=>14,
            'U'=>16,'V'=>10,'W'=>22,'X'=>25,'Y'=>24,'Z'=>23,
        ];
        $even = array_combine(
            array_merge(range('0', '9'), range('A', 'Z')),
            array_merge(range(0, 9), range(0, 25))
        );

        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $sum += ($i % 2 === 0 ? $odd : $even)[$value[$i]];
        }

        return chr(ord('A') + ($sum % 26)) === $value[15];
    }

    public static function sex(string $value): ?string
    {
        $value = strtoupper(trim($value));
        if (! self::isValid($value) || substr($value, 0, 3) === 'STP' || substr($value, 0, 3) === 'ENI') {
            return null;
        }
        $day = strtr(substr($value, 9, 2), self::HOMOCODIA);
        if (! ctype_digit($day)) {
            return null;
        }

        return (int) $day > 40 ? '2' : '1';
    }
}
