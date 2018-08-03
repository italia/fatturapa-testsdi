all:
	cp node_modules/vue/dist/vue.js www/js/.
	cp node_modules/vue/dist/vue.min.js www/js/.

	cp node_modules/bootstrap/dist/css/bootstrap.min.css www/css/.
	cp node_modules/@fortawesome/fontawesome-free/css/all.min.css www/css/.
	cp node_modules/jquery/dist/jquery.min.js www/js/.
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js www/js/.

	mkdir -p www/webfonts
	cp node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2 www/webfonts/.
	cp node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff www/webfonts/.
	cp node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.ttf www/webfonts/.

	cp node_modules/bootstrap-italia/dist/css/bootstrap-italia.min.css www/css/.
	cp node_modules/bootstrap-italia/dist/css/italia-icon-font.css www/css/.