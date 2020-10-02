<html>
    <head>
        <title>Program output comparison</title>
        <link rel="stylesheet" type="text/css" href="diffStyle.css" />
    </head>
    <body>
        <?php
        // include the Diff class
        require_once './class.Diff.php';
        ini_set('max_execution_time', 300);
        // input file to test
        $prog = file_get_contents($_POST["inputfile"]);
        $paramval = urlencode($prog);
        // no need to sanitize, since everything except the instructor's program runs on the same computer
        $url1 = "https://cssrvlab01.utep.edu/classes/cs5339/longpre/assignment1/Fall20programInstructor.php?inputfile=$paramval";
        // remove carriage return characters hopefully to make it work for linux, mac and windows
        // $s1 is the output of the instructor's program
        $s1 = str_replace("\x0d", '', file_get_contents($url1));
        // no real need for sanitize, but why not?       
        $url2 = filter_var($_POST['evaluator'], FILTER_SANITIZE_URL);
        // $s2 is the output of the student's program
        $s2 = str_replace("\x0d", '', file_get_contents($url2));
        // display the comparison of $s1 and $s2
        $result = Diff::toTable(Diff::compare($s1, $s2));
        echo "Left is instructor's program, right the program from the form.<br/>" . PHP_EOL;
        echo "Anything highlighted green or pink indicates discrepancies.";
        echo $result;
        ?>
    </body>
</html>