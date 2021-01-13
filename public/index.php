<!DOCTYPE html>


<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Splitter web!</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
	</head>

<body>
<br>
<br>
<form method="post" enctype="multipart/form-data">
    Afegiu el PDF amb les actes descarregat de SAGA:
    <input type="file" name="actaToUpload" id="fileToUpload">
    <input type="submit" value="Comptar el total d'UF avaluades i aprovades" name="submitActes">
</form>
<?php 

    define("PYTHON_ACTES_SCRIPT", "UFAprovadesVsAvaluades.py");
    define("PYTHON_ACTES_SCRIPT_DIR", "../batch/");
    define("TARGET_ACTES_DIR", "../uploads/");

    $fileType = "pdf";
    $target_file = TARGET_ACTES_DIR.time().".".$fileType;
    $uploadOk = true;

    if (isset($_FILES["actaToUpload"])) {
        $uploaded_file = $_FILES["actaToUpload"];
    }
    if (isset($_FILES["actaToUpload"]["size"])) {
        $uploaded_size = $_FILES["actaToUpload"]["size"];
    }

    // Check PDF file type
    if(isset($_POST["submit"])) {
        $submit = $_POST["submitActes"];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        
        $uploaded_file_tmp_name = $_FILES["actaToUpload"]["tmp_name"];
        
        if(finfo_file($finfo, $uploaded_file_tmp_name) === 'application/pdf') {
            $uploadOk = true;
        } else {
            $uploadOk = false;
        }
        finfo_close($finfo);
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "<br>El fitxer ja existeix. ";
        $uploadOk = false;
    }

    // Check file size
    if ($uploaded_size > 25000000) {
        echo "<br>El fitxer pujat és massa gran. ";
        $uploadOk = false;
    }

    // Check file is not empty
    if (isset($_POST["submitActes"]) && $uploaded_size == 0) {
        echo "<br>El fitxer pujat està buit. ";
        $uploadOk = false;
    }

    // Allow certain file formats
    if($fileType != "pdf" ) {
        echo "<br>Només es poden pujar fitxers PDF. ";
        $uploadOk = false;
    }

    // Check if $uploadOk is set to 0 by an error
    if (!$uploadOk) {
        echo "<br>No s'ha pogut pujar el seu fitxer. ";

    // if everything is ok, try to upload file
    } else {

        if (move_uploaded_file($uploaded_file["tmp_name"], $target_file)) {

            $command = "python3 '".PYTHON_ACTES_SCRIPT_DIR.PYTHON_ACTES_SCRIPT."' '".$target_file."'";
            $output = shell_exec($command);

            echo $output;

            // Clear uploaded file
            system("rm -rf ../uploads/*.pdf", $retval);
        }
    }
?>
<br>
<br>
<br>
<br>
<br>
<br>
<hr>
<br>
<br>
<form action="" method="post" enctype="multipart/form-data">
    Afegiu el PDF amb els butlletins descarregat de SAGA:
    <input type="file" name="butlletinsToUpload" id="butlletinsToUpload">
    <input type="submit" value="Descarregar ZIP amb butlletins individuals" name="submitButlletins">
</form>
<?php

define("PYTHON_BUTLLETINS_SCRIPT", "ButlletinsSplitter.py");
define("PYTHON_BUTLLETINS_SCRIPT_DIR", "../batch/");
define("RESULT_BUTLLETINS_ZIP_FILE", "butlletins.zip");
define("RESULT_BUTLLETINS_ZIP_FILE_DIR", "../batch/tmp/");
define("TARGET_BUTLLETINS_DIR", "../uploads/");

$uploaded_file = $_FILES["butlletinsToUpload"]["tmp_name"];
$fileType = "pdf";
$target_file = TARGET_BUTLLETINS_DIR.time().".".$fileType;
$uploadOk = true;

// Check PDF file type
if(isset($_POST["submitButlletins"])) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    
    if(finfo_file($finfo, $uploaded_file) === 'application/pdf') {
        $uploadOk = true;
    } else {
        $uploadOk = false;
    }
    finfo_close($finfo);
}

// Check if file already exists
if (file_exists($target_file)) {
    echo "<br>El fitxer ja existeix. ";
    $uploadOk = false;
}

// Check file size
if ($_FILES["butlletinsToUpload"]["size"] > 25000000) {
    echo "<br>El fitxer pujat és massa gran. ";
    $uploadOk = false;
}

// Check file is not empty
if (isset($_POST["submitButlletins"]) && $_FILES["butlletinsToUpload"]["size"] == 0) {
    echo "<br>El fitxer pujat està buit. ";
    $uploadOk = false;
}

// Allow certain file formats
if($fileType != "pdf" ) {
    echo "<br>Només es poden pujar fitxers PDF. ";
    $uploadOk = false;
}

// Check if $uploadOk is set to 0 by an error
if (!$uploadOk) {
    echo "<br>No s'ha pogut pujar el seu fitxer. ";

// if everything is ok, try to upload file
} else {

    if (move_uploaded_file($_FILES["butlletinsToUpload"]["tmp_name"], $target_file)) {
        #echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.\n";

        // Call the ButlletinsSplitter        
        $ret_val = exec("python3 ".PYTHON_BUTLLETINS_SCRIPT_DIR.PYTHON_BUTLLETINS_SCRIPT." ".$target_file, $ret_val);
        echo $ret_val;
      
        // Download zip file
        $file = RESULT_BUTLLETINS_ZIP_FILE_DIR.RESULT_BUTLLETINS_ZIP_FILE;

        if (headers_sent()) {
            echo 'HTTP header already sent';
        } else {
            if (!is_file($file)) {
                header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
                echo "<br>No s'ha trobat el fitxer. ";
            } else if (!is_readable($file)) {
                header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
                echo "<br>El fitxer no es pot llegir. ";
            } else {
                header($_SERVER['SERVER_PROTOCOL']." 200 OK");
                header("Content-type: application/zip"); 
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: ".filesize($file));
                header("Content-Disposition: attachment; filename=\"".basename($file)."\"");

                // Disable cache
                header("Expires: " .gmdate('D, d M Y H:i:s') ." GMT");
                header("Cache-control: private");
                header("Pragma: private");

                ob_end_clean();
                readfile($file);

                // Delete file
                unlink($file);

                // Clear temp zip file
                system("rm -rf ../batch/tmp/".RESULT_ZIP_FILE, $retval);

                // Clear uploaded file
                system("rm -rf ../uploads/*.pdf", $retval);

                exit();
            }
        }

    } else if(isset($_POST["submitButlletins"])) {
        echo "<br>S'ha produït un error en pujar el seu fitxer. ";
    }
}
?>
</body>
</html>
