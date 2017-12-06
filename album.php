<!-- Submitted By: Neil Jones ( 1001371689 ) -->
<?php
// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');

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
 
if(isset($_POST['sendfile'])){
    $uploaddir = getcwd()."/";
    $uploadfile = $uploaddir . basename($_FILES['file']['name']);
    echo move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
    $dropbox->UploadFile($_FILES['file']['name']);
    header("Refresh:0");
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
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Upload Image</h3>
                    </div>
                    <div class="panel-body">
                        <form action="album.php" method="POST" enctype="multipart/form-data">
                            <input name="file" type="file" />
                            <br/>
                            <input type="submit" value="sendfile" name="sendfile" />
                        </form>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Images in the Dropbox folder</h3>
                    </div>
                    <div class="panel-body">
                        <?php
                            $files = $dropbox->GetFiles("",false);
                            if(!empty($files)) {
                                $t = array_keys($files); 
                                for($i=0;$i<sizeof($t);$i++){
                                     $file = current($files);
                                     echo "<a href='album.php?view=$file->rev'>".substr($file->path,1)."</a>";
                                     echo date("Y.m.d");
                                    next($files);
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Current Image</h3>
                    </div>
                    <div class="panel-body">
                        Panel content
                    </div>
                </div>
            </div>
        </div>

    </body>

    </html>