$ftpUser = "if0_42267378"
$ftpPass = "Ebi0723497723"
$creds   = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

function Ftp-Upload {
    param([string]$localPath, [string]$uri)
    $r = [System.Net.FtpWebRequest]::Create($uri)
    $r.Method        = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $r.Credentials  = $creds
    $r.UseBinary     = $true
    $r.UsePassive    = $true
    $r.KeepAlive     = $false
    $bytes = [IO.File]::ReadAllBytes($localPath)
    $r.ContentLength = $bytes.Length
    $s = $r.GetRequestStream()
    $s.Write($bytes, 0, $bytes.Length)
    $s.Close()
    $r.GetResponse().Close()
}

Ftp-Upload "C:\xampp\htdocs\Foodsaver\probe.php" "ftp://ftpupload.net/htdocs/probe.php"
Write-Host "probe.php uploaded successfully" -ForegroundColor Green
