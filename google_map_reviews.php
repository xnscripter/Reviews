/*
Get Google-Reviews with PHP (7.4) cURL & without API Key
=====================================================
$options = array(
    'cid' 		=> '2498492881783103593', 	// Customer Identification (CID)
    'lang' 		=> 'en',                  	// give you language for auto translate reviews
    'min_rating'	=> 3,                 		// (0-4) only show reviews with more than x stars
    'sort'		=> true                 	// true = sort by rating (best first)
);
=====================================================
How to find the needed CID No:
  - use: [https://pleper.com/index.php?do=tools&sdo=cid_converter]
  - and do a search for your business name
*/

function getReviews($o) {
	extract($o);
	$ch = curl_init();
	curl_setopt_array($ch, 
		array(
			CURLOPT_URL => 'https://www.google.com/maps?cid='.($cid ?? '2498492881783103593'),
			CURLOPT_HTTPHEADER => array('Accept-Language: '.($lang ?? 'ru')),
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36',
			CURLOPT_HEADER => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_RETURNTRANSFER => 1
		)
	);
	$r = [];
	if (preg_match('/window\.APP_INITIALIZATION_STATE\=(.*);window\.APP_FLAGS=/ms', curl_exec($ch), $m)) {
		curl_close($ch);
		$m = json_decode($m[1]);
		$m = json_decode(ltrim($m[3][6], ")]}'"));	
		$reviews = $m[6][52][0] ?? array();
		$min_rating ??= 0;
		foreach($reviews as $el) {
			if ($el[4] < $min_rating) { continue; }
			$r[] = array(
				'text' => $el[3],
				'rate' => $el[4],
				'age' => $el[1],
				'date' => date("d.m.Y H:i:s",round($el[27]/1000)),
				'a_name' => $el[0][1],
				'a_image' => $el[0][2],
				'a_link' => $el[0][0]
			);
		}
		$sort ??= false;
		if ($sort) { array_multisort(array_column($r,'rate'), SORT_DESC, $r); }
		$r = array(
			'company' => $m[6][11],
			'address' => $m[6][39],
			'logotype' => $m[6][157],
			'reviews' => $r
		);
	}
	return $r;
}

print_r(
  getReviews(
    array(
      'cid' 		=> '4676976056870247934',
      'lang' 		=> 'en',
      'min_rating'	=> 3,
      'sort'		=> true
    )
  )
);
