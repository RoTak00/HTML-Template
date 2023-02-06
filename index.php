<?php
    function strpos_all($haystack, $needle) {
        $offset = 0;
        $allpos = array();
        while (($pos = strpos($haystack, $needle, $offset)) !== FALSE) {
            $offset   = $pos + 1;
            $allpos[] = $pos;
        }
        return $allpos;
    }

    $example = htmlspecialchars("<h1>Example:</h1><div>Hello, [[name]]! You have [[amount]][[currency]] left in your account.</div>");

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $posted = true;
        $bulk_data = [];
        $fp = fopen($_FILES['file']['tmp_name'], "r");

        

        // get the column titles
        $titles = fgetcsv($fp, intval(0), ";");
        $bulk_data []= $titles;
        $cnt_columns = count($titles);

        $output_data = [];
        $template = $_POST['template'];

        $tags_begin = strpos_all($template, "[[");
        $tags_end = strpos_all($template, "]]");

        if(count($tags_begin) !== count($tags_end))
        {
           $error = "tags";
        }

        $tags = [];
        for($i = 0; $i < count($tags_begin); $i += 1)
        {
            $tags []= substr($template, $tags_begin[$i] + 2, $tags_end[$i] - $tags_begin[$i] - 2);

            if(!in_array($tags[$i], $titles))
            {
                $error = "not_exist";
                $which = $tags[$i];
            }
        }


        while($item = fgetcsv($fp, intval(0), ";"))
        {
            $output = $template;
            foreach($item as $i=>$el)
            {
                $output = str_replace("[[".$titles[$i]."]]", $el, $output);
            }
            $output_data []= $output;
            $bulk_data []= $item;
        }

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        .div-form
        {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        input 
        {
            display: block;
            font-size: 20px;
        }
        button{
            display: block;
            width: 100%;
            height: 50px;
            font-size: 20px;

        }

        .div-errors
        {
            width: 100%;
        }
        .div-errors p {
            color: red;
            font-size: 22px;
            text-align: center;
        }

        .wrapper{
            margin-left: 10px;
            margin-right: 10px;
            display: flex;
            justify-content: space-around;
            width: 100%;
            align-items: flex-start;
        }

        .div-left{
            width: 25%;
        }

        .h1{
            text-align: center;
        }

        .div-right {
            width: 25%;
        }

        .div-main {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;;
        }

        .footer
        {
            display: flex;
            justify-content: center;
            margin-top: 100px;
        }
        .footer > p
        {
            text-align: center;
        }

        .div-output{
            width: 90%;
            height: 25vh;
            overflow-x: scroll;
            overflow-y: scroll;
            border: 3px solid black;
        }
    </style>


</head>
<body>

    <div class = "wrapper">

    <div class = "div-left">
        <h1 class = "h1">Guide</h1>

        <div>
            <ol>
                <li>
                    Set up your bulk data in a .csv file with headers, separated by <strong>;</strong>
                </li>
                <li>
                    Create your html template, and set up placeholders with [[column_name]], like in the example.
                </li>
                <li>
                    Make sure every opening tag is closed! Make sure every placeholder you use is present as a column in the .csv file!
                </li>
                <li>
                    Upload the .csv file when the template is finished.
                </li>
                <li>
                    Click on "generate". You may now copy the source code generated.
                </li>
            </ol>
        </div>
    </div>

    <div class = "div-main">
        <div>
        <h1 class = "h1"> Template and Input </h1>
        <?php

        if(isset($error))
        {
            ?> <div class = "div-errors"> <?php
            if($error == "tags")
                echo "<p>Tags inccorectly set</p>";

            if($error == "not_exist")
                echo "<p>Tag ".$which." does not exist</p>";

            echo "</div>";
        }
        
        ?>
        <div class = "div-form">
        <form method = "POST" action = "index.php" enctype="multipart/form-data">
            <textarea style = "width: 500px;height:500px;" name = "template"><?=$_POST['template']??$example?></textarea>
            <input type="file" name = "file" accept=".txt,.csv" required>
            <button type = "submit">Generate</button>
        </form>
        </div>
    </div>

        <?php
        if(isset($posted) && !isset($error))
        { ?>

            <div style = "margin-left: auto; margin-right: auto;">
                <h2> Bulk .csv data </h2>
                <table>
                <?php
            foreach($bulk_data as $i=>$row)
            {
                echo "<tr>";
                foreach($row as $item)
                    echo ($i == 0 ? "<th>" : "<td>").$item.($i == 0 ? "</th>" : "</td>");

                echo "</tr>";
            }
            echo "</table></div>";
        }
        ?>

    </div>

    <div class = "div-right">
    <h1 class = "h1"> Results </h1>

        <?php
        if(isset($posted) && !isset($error))
        {
            ?><textarea style = "width: 90%; height: 500px;"><?php
                foreach($output_data as $el)
                {
                    echo $el;
                }
            echo "</textarea><hr>";
        }
        if(isset($posted) && !isset($error))
        {   ?> <div class = "div-output"> <?php
            foreach($output_data as $el)
            {
                echo $el;
            }
            echo "</div>";
        }

        ?>
    </div>
    </div>

    <div class = "footer">
        <p>Created by&nbsp;<a href = "https://www.rotak.ro" target = "_blank">RoTak</a>. Trademark <?=date("Y")?><br>
        For contact and more information, access&nbsp;<a href = "https://www.rotak.ro" target = "_blank">www.rotak.ro</a></p>
    </div>
    
</body>
</html>