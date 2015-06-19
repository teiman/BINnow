
foreach $line(<DATA>){
	chomp $line;
	
	$name2 = $line;
	$name2 =~ s/\.png/\.gif/g;
	
	
	#print $line . "=>" . $name2 . "\n";
	system("convert $line $name2 ");
	
	
	
}


__DATA__
1downarrow.png
1rightarrow.png
1uparrow.png
addcliente.png
advanced-directory.png
ark.png
attach.png
bar16.png
bg1.png
bg2.png
bg4.png
bg5.png
bg.png
borrarcliente.png
button_cancel.png
button_ok.png
cart.png
cdrom_mount.png
channel1.png
cliente16 (copia).png
cliente16.png
clock.png
colorprint16.png
colorprint30.png
conexion.png
config16.png
contacto.png
contents.png
day_grid.png
defecto1.png
del.png
doc.png
document.png
editcopy.png
editcut.png
editdelete.png
editicon.png
edit.png
error.png
estadisticas.png
exit16.png
exit.png
factura.png
facturas.png
filefind.png
find16.png
forward.png
gallery.png
green_h.png
headerback2.png
headerback.png
health.png
help.png
helpred.png
hi1.png
icon-info.png
important.png
info.png
kbackgammon_engine.png
keditbookmarks.png
kpackage.png
left_arrow.png
listado.png
listados.png
logodpiextendido.png
logodpiw24.png
logodpiw49.png
looknfeel.png
m
mail_delete.png
mail_find.png
mail_generic.png
message.png
modcliente.png
more.png
network.png
nfs_unmount.png
niceinfo.png
nota.png
package_favourite.png
personal.png
player_pause.png
presupuestos.png
printicon.png
producto16.png
proveedor16.png
right_arrow.png
run.png
searchicon.png
spreadsheet.png
stock16.png
stock.png
stop.png
sugerencia1.png
tex.png
tpv1.png
tpvgrande.png
usuarios.png
wrule.png
xmlrpcinspector-toolbar.png
yast_partitioner.png
