function loadGoogleMaps(){
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'https://maps.googleapis.com/maps/api/js?sensor=false&' + 'callback=googleMapsLoaded';
	document.body.appendChild(script);	
}

function googleMapsLoaded(){

	$('.qwik-gmaps').each(function(){
		var $this = $(this);
		var $gmapContainer = $(this).find('.qwik-gmaps-map');
		//var gmap = getMap(gmapContainer.get(0));
		$this.find('.qwik-gmaps-address').each(function(){
			var $address = $(this);
			
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
			
			//On met l'adresse dans les datas
			$address.data('address', address);
			
			//On met aussi l'icone
			var icon = '';
			if($address.find('.qwik-gmaps-address-icon').length > 0){
				icon = $address.find('.qwik-gmaps-address-icon').attr('src');
			}
			$address.data('icon', icon);
			
			//On ajoute l'addresse à la carte gmaps
			addToGmap($gmapContainer, $address);
		});
	});
}


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


//Ajout de l'adresse dans la map. Center permet de savoir si on centre la carte sur l'adresse donn�e
function addToGmap($gmapContainer, $address){
	var geocoder = new google.maps.Geocoder(); 

	//On trouve l'adresse, grace à google...
	geocoder.geocode( { 'address': $address.data('address')}, function(results, status) {
		//Si on a trouvé une adresse
		if (status == google.maps.GeocoderStatus.OK) {
			var myLatLng = results[0].geometry.location;
			gmap = $gmapContainer.data('gmap');
			if(gmap == null){
				gmap = getMap($gmapContainer.get(0), myLatLng);
				$gmapContainer.data('gmap', gmap);
				$gmapContainer.data('bounds', new google.maps.LatLngBounds());

				//Max zoom à 14 (TODO: à mettre dans la config) + tester avec plusieurs adresses
				var listener = google.maps.event.addListener(gmap, "idle", function() {
					if (gmap.getZoom() > 14){
						gmap.setZoom(14); 
					}
					google.maps.event.removeListener(listener); 
				});
			}
			//Nouveau marker
			var marker = new google.maps.Marker({
				map: gmap,
				icon: $address.data('icon'),
				position: myLatLng,
				html : '<div class="qwik-gmaps-address-window">' + $address.html() + '</div>'
			});

			//Si c'est centré on met le marker, sinon on attend le "onclick"
			var infoWindow = new google.maps.InfoWindow({
			    content: marker.html
			});

			$address.data('marker', marker);
			$address.data('infoWindow', infoWindow);
			$address.data('map', gmap);

			google.maps.event.addListener(marker, "mouseover", function () {
				$address.addClass('qwik-gmaps-address-over');
				infoWindow.open(gmap, marker);
	        });
			google.maps.event.addListener(marker, "mouseout", function () {
				$address.removeClass('qwik-gmaps-address-over');
				infoWindow.close();
	        });
			$gmapContainer.data('bounds').extend(myLatLng);

			//Hop, on fait en sorte que tout le monde rentre
			$gmapContainer.data('gmap').fitBounds($gmapContainer.data('bounds'));

		}
	});
}

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

loadGoogleMaps();
