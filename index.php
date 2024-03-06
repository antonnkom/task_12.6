<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Практическая работа, базовый PHP, типы данных</title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php'; ?>
</head>
<body>
    <header></header>

    <main>
        <section id="examples">
            <div>
                <pre><?php print_r(getPartsFromFullname('аль-Хорезми Мухаммад ибн-Муса')); ?></pre>
            </div>

            <div>
                <pre><?= getFullnameFromParts('аль-Хорезми', 'Мухаммад', 'ибн-Муса') ?></pre>
            </div>

            <div>
                <pre><?= getShortName('да Винчи Леонардо ибн-Муса') ?></pre>
            </div>

            <div>
                <pre><?= getGenderFromName('КОМАров АНТОН оЛЕГОВич') ?></pre>
            </div>

            <div>
                <pre><?= getGenderDescription($examplePersonsArray) ?></pre>
            </div>

            <div>
                <pre><?= getPerfectPartner('КОМАров', 'АНТОН', 'оЛЕГОВич', $examplePersonsArray) ?></pre>
            </div>
        </section>
    </main>
    
    <footer></footer>
</body>
</html>