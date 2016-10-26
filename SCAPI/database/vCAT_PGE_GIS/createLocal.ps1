
$s = "(localdb)\MSSQLLocalDB"

sqlcmd -S $s -d master -i "vCAT_PGE_GIS.publish.sql"