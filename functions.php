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
    global $partsOfName;
    $surname = mb_convert_case($surname, MB_CASE_LOWER_SIMPLE);
    $name = mb_convert_case($name, MB_CASE_LOWER_SIMPLE);
    $patronomyc = mb_convert_case($patronomyc, MB_CASE_LOWER_SIMPLE);
    $partSurname = '';
    $partPatronomyc = '';

    foreach ($partsOfName as $value) {
        $conditionForSurname = (
            (mb_strpos($surname, $value . '-') !== false) || 
            (mb_strpos($surname, $value . ' ') !== false) ||
            (mb_strpos($surname, $value . '\'') !== false)
        );

        $conditionForPatronomyc = (
            (mb_strpos($patronomyc, $value . '-') !== false) || 
            (mb_strpos($patronomyc, $value . ' ') !== false)
        );

        if ($conditionForSurname) {
            $partSurname = $value;        
        }

        if ($conditionForPatronomyc) {
            $partPatronomyc = $value;
        }
    }

    $surname = getName($surname, $partSurname);
    $name = getName($name);
    $patronomyc = getName($patronomyc, $partPatronomyc);
    
    return "$surname $name $patronomyc";
}

/**
 * Разбиение полного имени на фамилию, имя и отчество
 * @param string $fullname
 * @return array
 */
function getPartsFromFullname(string $fullname) : array
{
    global $partsOfName;
    $fullname = mb_convert_case($fullname, MB_CASE_LOWER_SIMPLE);
    $arr = explode(' ', $fullname);

    if (in_array($arr[0], $partsOfName)) {
        $surname = getName("{$arr[0]} {$arr[1]}", $arr[0]);
        $name = getName($arr[2]);
        $patronomyc = getName($arr[3]);
    } else {
        // name
        $name = getName($arr[1]);

        // surname
        if (mb_strpos($arr[0], '-') !== false) {
            $arrSurname = explode('-', $arr[0]);
            $surname = getName($arr[0], $arrSurname[0]);
        } elseif (mb_strpos($arr[0], '\'') !== false) {
            $arrSurname = explode('\'', $arr[0]);
            $surname = getName($arr[0], $arrSurname[0]);
        } else {
            $surname = getName($arr[0]);
        }

        // patronomyc
        if (mb_strpos($arr[2], '-') !== false) {
            $arrPatronomyc = explode('-', $arr[2]);
            $patronomyc = getName($arr[2], $arrPatronomyc[0]);
        } else {
            $patronomyc = getName($arr[2]);
        }
    }

    return [
        'surname' => $surname,
        'name' => $name,
        'patronomyc' => $patronomyc,
    ];
}

/**
 * Получаем полную фамилию, имя или отчество в правильном виде
 * @param string $nameWithoutFormat
 * @param string $partName - частица фамилии или отчества, по умолчанию пустая строка
 * @return string
 */
function getName(string $nameWithoutFormat, string $partName = '') : string
{
    $nameWithoutFormat = str_replace(['-', '\'',], ' ', $nameWithoutFormat);
    $arr = explode(' ', $nameWithoutFormat);

    switch ($partName) {
        case 'аль':
        case 'ибн':
        case 'бен':
        case 'бин':
            $name = trim(array_shift($arr));            
            break;

        case 'д':
            $name = trim(array_shift($arr)) . '\'' . mb_convert_case(trim(array_shift($arr)), MB_CASE_TITLE_SIMPLE);
            break;

        case '':
            $name = mb_convert_case(trim(array_shift($arr)), MB_CASE_TITLE_SIMPLE);
            break;

        default:
            $name = trim(array_shift($arr)) . ' ' . mb_convert_case(trim(array_shift($arr)), MB_CASE_TITLE_SIMPLE);
            break;        
    }

    while (count($arr) > 0) {
        $name .= '-' . mb_convert_case(trim(array_shift($arr)), MB_CASE_TITLE_SIMPLE);
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
    global $partsOfName;
    $arr = getPartsFromFullname($fullname);
    $shortSurname = '';

    foreach ($partsOfName as $partName) {
        if (mb_strpos($arr['surname'], $partName) !== false) {
            $shortSurname = getShortSurname($arr['surname'], [' ', '-', '\'',]);
            break;
        }
    }

    $shortSurname = ! empty($shortSurname) ? $shortSurname : mb_substr($arr['surname'], 0, 1);
    return $arr['name'] . ' ' . $shortSurname . '.';
}

/**
 * Получение сокращения фамилии
 * @param string $surname полная фамилия
 * @param array $separator разделитель
 * @return string
 */
function getShortSurname(string $surname, array $separator) : string
{
    foreach ($separator as $s) {
        if (mb_strpos($surname, $s) !== false) {
            $arrSurname = explode($s, $surname);
            return $arrSurname[0] . $s . mb_substr($arrSurname[1], 0, 1);
        }
    }
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
        $gender += 1;
    } elseif (mb_substr($arr['name'], -1) === 'н') {
        $gender += 1;
    } elseif (mb_substr($arr['name'], -1) === 'a') {
        $gender -= 1;
    }

    if (mb_substr($arr['surname'], -1) === 'в') {
        $gender += 1;
    } elseif (mb_substr($arr['surname'], -2) === 'ва') {
        $gender -= 1;
    }

    if (mb_substr($arr['patronomyc'], -2) === 'ич') {
        $gender += 1;
    } elseif (mb_substr($arr['patronomyc'], -3) === 'вна') {
        $gender -= 1;
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
            $countMale += 1;
        } elseif ($gender === -1) {
            $countFemale += 1;
        }

        $countPersons += 1;
    }

    $percentMale = round($countMale * 100 / $countPersons, 1);
    $percentFemale = round($countFemale * 100 / $countPersons, 1);
    $percentUndefined = round(($countPersons - ($countMale + $countFemale)) * 100 / $countPersons, 1);

    $percentMale = number_format((float)$percentMale, 1, '.', '');
    $percentFemale = number_format((float)$percentFemale, 1, '.', '');
    $percentUndefined = number_format((float)$percentUndefined, 1, '.', '');

    return <<<HERERESULT
    <h3>Гендерный состав аудитории:</h3>
    Мужчины - $percentMale%
    Женщины - $percentFemale%
    Не удалось определить - $percentUndefined%
    HERERESULT;
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
