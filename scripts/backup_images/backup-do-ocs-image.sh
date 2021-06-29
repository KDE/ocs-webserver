########## Configuration ############
ROEMTEBACKUPFILES="/mnt/volume_fra1_01/img/0/0/0/0/" # zu sichernde Verzeichnisse mit Leerzeichen getrennt
REMOTE="10.135.209" # Rechner von dem gesichert wird
REMOTEUSER="root" # User, auf den via ssh ohne Passwort zugegriffen wird
SUBJECT="Backup_fehlgeschlagen!" # im Subject kein Leerzeichen!
ERROR="./error.txt" # Text, der im Error-Fall versandt wird
MAILTO="./backup.mail" # Mailadressen, die im Error-Fall Mail erhalten

DATE=`/bin/date +%Y%m%d` # Datum im Format YearMonthDay
SSH=/usr/bin/ssh
KEY="-i /root/.ssh/do-ocs-images.pem"
HOST=`$SSH $KEY $REMOTEUSER\\@$REMOTE /bin/hostname`

CAT=/bin/cat
MAIL=/usr/bin/mail

LOCALFILE="backup_$HOST".$DATE."tgz" # Dateiname der Backup-Datei
#LOCALDIR="/mnt/volume_fra1_tier1_bkp/backups/files/do-ocs-images/"
LOCALDIR="/tmp/"
CHECK_REMOTE=`ping -c1 $REMOTE | grep received | awk '{print $4}'` # gibt 1 (online) oder 0 (offline) zurueck
#####################################

if [ $CHECK_REMOTE == 1 ]; then
echo "### start backup"
#$TAR $TAROPTIONS $ROEMTEBACKUPFILES | $GZIP | $SSH $REMOTEUSER\\@$REMOTE "cat > $REMOTEDIR$REMOTEFILE"
echo `$SSH $KEY $REMOTEUSER\\@$REMOTE tar cpzf - $ROEMTEBACKUPFILES > $LOCALDIR$LOCALFILE`
echo "### start finished"
else
echo "### server instance not reachable"
$CAT $MAILTO | while read line
do
$MAIL $line -s $SUBJECT &lt; $ERROR
done
fi