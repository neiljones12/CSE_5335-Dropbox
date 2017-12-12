<!-- Submitted By: Neil Jones ( 1001371689 ) -->

<?php
// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','off');

require_once 'demo-lib.php';
set_time_limit( 0 );
require_once 'DropboxClient.php';
$dropbox = new DropboxClient( array(
    'app_key' => "spwjhny40d153bm",
    'app_secret' => "bwdk79rsvu6b8k9",
    'app_full_access' => false,
) );

$return_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?auth_redirect=1";

$bearer_token = demo_token_load( "bearer" );

if ( $bearer_token ) {
    $dropbox->SetBearerToken( $bearer_token );
    //echo "loaded bearer token: " . json_encode( $bearer_token, JSON_PRETTY_PRINT ) . "\n";
} elseif ( ! empty( $_GET['auth_redirect'] ) )
{
    $bearer_token = $dropbox->GetBearerToken( null, $return_url );
    demo_store_token( $bearer_token, "bearer" );
} elseif ( ! $dropbox->IsAuthorized() ) {
    $auth_url = $dropbox->BuildAuthorizeUrl( $return_url );
    die( "Authentication required. <a href='$auth_url'>Continue.</a>" );
}

$files = $dropbox->GetFiles( "", false );

if(isset($_POST['Upload'])){
    $uploaddir = getcwd()."/";
    $uploadfile = $uploaddir . basename($_FILES['file']['name']);
    echo move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
    $dropbox->UploadFile($_FILES['file']['name']);
    header("Refresh:0");
} 

if(isset($_GET["delete"])){
    reset($files);
    for($i=0;$i<sizeof($files);$i++){
        $f = current($files);
        if($f->rev == $_GET["delete"]){
          $dropbox->Delete($f->path);
          header("Refresh:0");
        }
        next($files);                   
    }
}
?>
<html>
    <head>
        <title>Project 8 - Neil Jones</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
            crossorigin="anonymous">
    </head>

    <body style="padding-top:30px">
        <div class="col-md-12">
            <h3>Project 8</h3>
            <p>Submitted By: Neil Jones (1001371689)</p>
            <hr>
        </div>
        <div class="col-md-12">
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Upload Image</h3>
                    </div>
                    <div class="panel-body">
                        <!-- HTML form to upload an image -->
                        <form action="album.php" method="POST" enctype="multipart/form-data">
                            <input class='btn btn-default' name="file" type="file" />
                            <br/>
                            <input class='btn btn-default' type="submit" value="Upload" name="Upload" />
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Images in the Dropbox folder</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Images</th>
                                    <th>Date</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Getting the images from dropbox to display the list
                                    $files = $dropbox->GetFiles("",false);
                                    if(!empty($files)) {
                                        $t = array_keys($files); 
                                        for($i=0;$i<sizeof($t);$i++){
                                            $file = current($files);
                                            echo '<tr>';
                                            echo "<td><a href='album.php?view=$file->rev'>".substr($file->path,1)."</a></td>";
                                            echo '<td>'.date("Y.m.d").'</td>';
                                            echo "<td><a class='btn btn-info' href='album.php?view=$file->rev'>View</a></td>";
                                            echo '</tr>';
                                            next($files);
                                        }
                                    }
                                ?> 
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Current Image</h3>
                    </div>
                    <div class="panel-body">
                        <div class="text-center">
                            <?php
                                // Showing the current image that has been selected by the user
                                if(isset($_GET["view"])){
                                    reset($files); 
                                    for($i=0;$i<sizeof($files);$i++){
                                        $f = current($files);
                                        $test_file = basename($f->path);
                                        if($f->rev == $_GET["view"]){ 
                                            echo "<img class='img-responsive thumbnail' src='".$dropbox->GetLink($f,false)."'/></br>";
                                            echo '<p>'.$test_file.'</p>';
                                            $dropbox->DownloadFile($f, $test_file);
                                            echo "<form action=album.php method=\"get\">";
                                            echo "<input type=\"hidden\" value=$f->rev name=\"delete\">";
                                            echo "<input class='btn btn-danger btn-lg' type=\"submit\" value=\"delete\">";
                                            echo  "</form>";
                                        }
                                        next($files);
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </body>
</html>