all:
	cp node_modules/vue/dist/vue.js www/js/.
	cp node_modules/vue/dist/vue.min.js www/js/.

	cp node_modules/bootstrap/dist/css/bootstrap.min.css www/css/.
	cp node_modules/@fortawesome/fontawesome-free/css/all.min.css www/css/.
	cp node_modules/jquery/dist/jquery.min.js www/js/.
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js www/js/.
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map www/js/.

	cp node_modules/bootstrap-italia/dist/js/bootstrap-italia.bundle.min.js www/js/.
	cp node_modules/bootstrap-italia/dist/js/bootstrap-italia.min.js www/js/.


	mkdir -p www/webfonts
	cp node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2 www/webfonts/.
	cp node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff www/webfonts/.
	cp node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.ttf www/webfonts/.

	cp node_modules/bootstrap-italia/dist/css/bootstrap-italia.min.css www/css/.
	cp node_modules/bootstrap-italia/dist/css/italia-icon-font.css www/css/.

	./bin/build.php index "FatturaPA testUI - Dashboard" > www/index.html
	./bin/build.php sdi "FatturaPA testUI - Sistema di Interscambio" > www/sdi.html
	./bin/build.php td0000001 "FatturaPA testUI - Trasmittente / destinatario 00000001" > www/td0000001.html
	./bin/build.php td0000002 "FatturaPA testUI - Trasmittente / destinatario 00000002" > www/td0000002.html
