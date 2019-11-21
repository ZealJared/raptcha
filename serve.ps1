Set-Location public
$php = Start-Process php -ArgumentList '-S localhost:3000 index.php' -PassThru
Write-Host PHP running as $php.Id
Set-Location ../ui
$npm = Start-Process npm -ArgumentList 'run serve' -PassThru
Write-Host NPM running as $npm.Id
