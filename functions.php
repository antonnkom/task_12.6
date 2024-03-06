<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

/**
 * Объединение фамилии, имени и отчества в полное имя
 * @param string $name - имя
 * @param string $surname - фамилия
 * @param string $patronomyc - отчество
 * @return string
 */
function getFullnameFromParts(string $surname, string $name, string $patronomyc) : string
{
    $surname = mb_convert_case($surname, MB_CASE_LOWER_SIMPLE);
    $name = mb_convert_case($name, MB_CASE_LOWER_SIMPLE);
    $patronomyc = mb_convert_case($patronomyc, MB_CASE_LOWER_SIMPLE);

    return getFullFormName($surname) . ' ' . getFullFormName($name) . ' ' . getFullFormName($patronomyc);
}

/**
 * Разбиение полного имени на фамилию, имя и отчество
 * @param string $fullname
 * @return array
 */
function getPartsFromFullname(string $fullname) : array
{
    $fullname = mb_convert_case($fullname, MB_CASE_LOWER_SIMPLE);
    $arrFullname = explode(' ', $fullname);
    
    return [
        'surname' => getFullFormName($arrFullname[0]),
        'name' => getFullFormName($arrFullname[1]),
        'patronomyc' => getFullFormName($arrFullname[2]),
    ];
}

/**
 * Получение Фамилии или Отчества с частицей в правильном регистре
 * @param string $name
 * @return string
 */
function getFullFormName(string $name) : string
{
    $name = trim($name);
    $arrName = explode('-', $name);
    
    if (count($arrName) > 1) {
        $name = array_shift($arrName);

        foreach ($arrName as $aName) {
            $name .= '-' . mb_convert_case($aName, MB_CASE_TITLE_SIMPLE);
        }
    } else {
        $name = mb_convert_case($name, MB_CASE_TITLE_SIMPLE);
    }

    return $name;
}

/**
 * Получение сокращённого имени
 * @param string $fullname
 * @return string
 */
function getShortName(string $fullname) : string
{
    $arrFullname = getPartsFromFullname($fullname);
    $arrSurname = explode('-', $arrFullname['surname']);

    if (count($arrSurname) > 1) {
        $shortSurname = $arrSurname[0] . '-' . mb_substr($arrSurname[1], 0, 1) . '.';
    } else {
        $shortSurname = mb_substr($arrSurname[0], 0, 1) . '.';
    }

    return "{$arrFullname['name']} {$shortSurname}";
}

/**
 * Определение пола по имени
 * @param string $fullname
 * @return int
 */
function getGenderFromName(string $fullname) : int
{
    global $partsOfName;
    $arr = getPartsFromFullname($fullname);
    $gender = 0;

    if (mb_substr($arr['name'], -1) === 'й') {
        $gender++;
    } elseif (mb_substr($arr['name'], -1) === 'н') {
        $gender++;
    } elseif (mb_substr($arr['name'], -1) === 'a') {
        $gender--;
    }

    if (mb_substr($arr['surname'], -1) === 'в') {
        $gender++;
    } elseif (mb_substr($arr['surname'], -2) === 'ва') {
        $gender--;
    }

    if (mb_substr($arr['patronomyc'], -2) === 'ич') {
        $gender++;
    } elseif (mb_substr($arr['patronomyc'], -3) === 'вна') {
        $gender--;
    }

    return $gender <=> 0;
}

/**
 * Определение полового состава аудитории
 * @param array $personsArray 
 * @return string
 */
function getGenderDescription(array $personsArray) : string
{
    $countMale = 0;
    $countFemale = 0;
    $countPersons = 0;

    foreach ($personsArray as $person) {
        $gender = getGenderFromName($person['fullname']);
        
        if ($gender === 1) {
            $countMale++;
        } elseif ($gender === -1) {
            $countFemale++;
        }

        $countPersons++;
    }

    $percentMale = round($countMale * 100 / $countPersons, 1);
    $percentFemale = round($countFemale * 100 / $countPersons, 1);
    $percentUndefined = round(($countPersons - ($countMale + $countFemale)) * 100 / $countPersons, 1);

    $percentMale = number_format((float)$percentMale, 1, '.', '');
    $percentFemale = number_format((float)$percentFemale, 1, '.', '');
    $percentUndefined = number_format((float)$percentUndefined, 1, '.', '');

    return <<<RESULT
    <h3>Гендерный состав аудитории:</h3>
    Мужчины - $percentMale%
    Женщины - $percentFemale%
    Не удалось определить - $percentUndefined%
    RESULT;
}

/**
 * Идеальный подбор пары
 * @param string $surname
 * @param string $name
 * @param string $patronomyc
 * @param array $personsArray
 * @return string
 */

 function getPerfectPartner(string $surname, string $name, string $patronomyc, array $personsArray) : string
 {
    $fullname = getFullnameFromParts($surname, $name, $patronomyc);
    $gender = getGenderFromName($fullname);

    if ($gender) {
        do {
            $key = rand(0, (count($personsArray) - 1));
            $genderPerson = getGenderFromName($personsArray[$key]['fullname']);
        } while ($gender === $genderPerson || ! $genderPerson);

        $shortName = getShortName($fullname);
        $shortPersonName = getShortName($personsArray[$key]['fullname']);
        $percent = number_format((float)round(rand(5000, 10000) / 100, 2), 2, '.', '');

        return <<<RESULT
        $shortName + $shortPersonName = 
        &#10084; Идеально на $percent% &#10084;
        RESULT;
    }
    
    return 'не удалось определить пол';
 }
