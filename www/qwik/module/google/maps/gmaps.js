
//Googlemap est loadé
function googleMapsLoaded(){
    //On trouve toutes les maps de la page
	$('.qwik-gmaps').each(function(){
		var $this = $(this);
		var $gmapContainer = $(this).find('.qwik-gmaps-map');
        //On trouve toutes les adresses de la map
		$this.find('.qwik-gmaps-address').each(function(){
			var $address = $(this);

            //On récupère l'icone si elle a été précisée
            var icon = '';
            if($address.find('.qwik-gmaps-address-icon').length > 0){
                icon = $address.find('.qwik-gmaps-address-icon').attr('src');
            }
            $address.data('icon', icon);

            //D'abord on check la latitude longitude. Si on l'a dans data, bingo, on ajoute la location sur la carte
            if($address.data('latitude') && $address.data('latitude')){
                var location = new google.maps.LatLng($address.data('latitude'), $address.data('longitude'));
                addLocationToGMap($gmapContainer, $address, location);
                return;
            }

            //On a pas de latitude/longitude, on va se débrouiller avec l'adresse
			//On fait une jolie adresse pour que google comprenne au mieux...
			var address = '';
			if($address.find('.qwik-gmaps-address-street').length > 0){
				address += $address.find('.qwik-gmaps-address-street').html();
			}
			if($address.find('.qwik-gmaps-address-number').length > 0){
				address += ' ' + $address.find('.qwik-gmaps-address-number').html();
			}
			if($address.find('.qwik-gmaps-address-zip').length > 0){
				address += ', ' + $address.find('.qwik-gmaps-address-zip').html();
			}
			if($address.find('.qwik-gmaps-address-city').length > 0){
				address += ' ' + $address.find('.qwik-gmaps-address-city').html();
			}
			if($address.find('.qwik-gmaps-address-country').length > 0){
				address += ', ' + $address.find('.qwik-gmaps-address-country').html();
			}

			//On ajoute l'addresse à la carte gmaps
			addAddressToGmap($gmapContainer, $address, address);
		});
	});
}

//Initialisation de la map. myLatLng est l'endroit ou on centre la map
function initGmap($gmapContainer, myLatLng){

    gmap = getMap($gmapContainer.get(0), myLatLng);
    $gmapContainer.data('gmap', gmap);
    $gmapContainer.data('bounds', new google.maps.LatLngBounds());

    //Lorsqu'on a fini, si notre zoom est plus grand que 14, on remet à 14, sinon c'est trop "proche" de l'adresse
    var listener = google.maps.event.addListener(gmap, "idle", function() {
        if (gmap.getZoom() > 14){
            gmap.setZoom(14);
        }
        google.maps.event.removeListener(listener);
    });
}
//Ajout de l'adresse avec la latitude longitude
function addLocationToGMap($gmapContainer, $address, myLatLng){
    gmap = $gmapContainer.data('gmap');

    //instanciation de la map si en a pas encore dans notre data du container global de la carte. La première adresse sera celle où on centre la map
    if(gmap == null){
        initGmap($gmapContainer, myLatLng);
    }

    //Nouveau marker (pin)
    var marker = new google.maps.Marker({
        map: gmap,
        icon: $address.data('icon'),
        position: myLatLng,
        html : '<div class="qwik-gmaps-address-window">' + $address.html() + '</div>'
    });

    //Infowindow lorsqu'on passe sa souris sur le marker
    var infoWindow = new google.maps.InfoWindow({
        content: marker.html
    });

    $address.data('marker', marker);
    $address.data('infoWindow', infoWindow);
    $address.data('map', gmap);

    //Event lorsqu'on passe sa souris sur le marker, on affiche une fenetre avec l'adresse
    google.maps.event.addListener(marker, "mouseover", function () {
        $address.addClass('qwik-gmaps-address-over');
        infoWindow.open(gmap, marker);
    });
    //Event lorsqu'on enlève sa souris du marker, on cache la fenetre
    google.maps.event.addListener(marker, "mouseout", function () {
        $address.removeClass('qwik-gmaps-address-over');
        infoWindow.close();
    });
    //On rajoute une latitude à l'ensemble des adresses qui seront affichées sur la carte
    $gmapContainer.data('bounds').extend(myLatLng);

    //Hop, on fait en sorte que tout le monde entre dans la map. Donc ca dézoom et recentre s'il faut, merci google :)
    $gmapContainer.data('gmap').fitBounds($gmapContainer.data('bounds'));
}

//Ajout de l'adresse dans la map.
function addAddressToGmap($gmapContainer, $address, address){
    //Instance qui va nous permettre de retrouver la latitude/longitude d'une addresse
	var geocoder = new google.maps.Geocoder(); 

	//On trouve l'adresse, grace à google...
	geocoder.geocode( { 'address': address}, function(results, status) {
		//Si on a trouvé une adresse
		if (status == google.maps.GeocoderStatus.OK) {
            addLocationToGMap($gmapContainer, $address, results[0].geometry.location);
		}
	});
}
//Renvoi une nouvelle map google, centré sur myLatLng, avec des paramètres par défaut
function getMap(dom, myLatLng){
	var mapOptions = {
      zoom: 15,
      center: myLatLng,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      mapTypeControl: true,
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
		},
		navigationControl: true,
		streetViewControl: true
    };

    return new google.maps.Map(dom, mapOptions);
}

jQuery(function(){

    //Récupération de google maps quand on est prêt
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://maps.googleapis.com/maps/api/js?sensor=false&' + 'callback=googleMapsLoaded';
    document.body.appendChild(script);

    //Lorsque l'on passe sa souris sur l'adresse, on affiche l'infoWindow de la map (si on l'a)
    $('.qwik-gmaps-address').on('mouseenter',function(){
        var $this = $(this);
        if(!$this.data('infoWindow')){
            return;
        }
        $this.data('infoWindow').open($this.data('map'), $this.data('marker'));
        $this.addClass('qwik-gmaps-address-over');
    }).on('mouseleave', function(){
        var $this = $(this);
        if(!$this.data('infoWindow')){
            return;
        }
        $this.data('infoWindow').close();
        $this.removeClass('qwik-gmaps-address-over');
    });

});
