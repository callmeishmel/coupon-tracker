<?php

public function index() {

	$transactionData = $this->getRequestData();
	$transactionSummary = $transactionData->GetReportDetailFromRequestCode6Result->TransactionSummaries->TransactionSummary4;
	$transactionStatData = [];
	$transactionDescs = [];


	foreach($transactionSummary as $key => $transaction) {

		$transactionStats = [];

		if($transaction->{'TypeCodes'} !== "") {
			unset($transaction);
			continue;
		}

		// Build the formatted transactions list
		$transactionStats['original_desc'] = $transaction->{'Description'};
		$transactionStats['simplified_desc'] = implode(' ', $wordsInDesc);
		$transactionStats['decision_logic_desc'] = $transaction->{'Category'};

		array_push($transactionStatData, $transactionStats);

		array_push($transactionDescs, implode(' ', $wordsInDesc));

	}

	sort($transactionDescs);

	$transactionDescs = array_count_values($transactionDescs);

	return view('decisionLogicTest', compact('transactionData', 'transactionDescs', 'transactionStatData'));
}

public function _siftFullDescription($string) {
	$string = str_replace($filteredTerms, '', $string);
	$string = preg_replace('/\d{2}\/\d{2}\/\d{4}/', '', $string);
	$string = str_replace('/', ' ',  $string);

	// Break down into words
	$wordsInDesc = explode(' ', $string);

	// Check each word in string
	foreach($wordsInDesc as $k => $word) {

		// Remove order numbers (#123) and (xxxx123ABC)
		$regexArr = ['/[#][0-9]*/', '/x{2,}\S*/', '/[*]{2,}/', '/[-]{2,}/'];

		foreach($regexArr as $pattern) {

			preg_match($pattern, $word, $matches);

			if(count($matches) > 0 || $word === '') {
				if($pattern === '/[-]{2,}/') {
					unset($wordsInDesc[$k - 1]);
				}
				unset($wordsInDesc[$k]);
			}

		}

		if(strpos($word, '*') !== false) {
			if(preg_match('/[.]\S{2,3}/', $word) !== false) {
				$wordsInDesc[$k] = str_replace('*', ' ', $word);
			} else {
				$wordsInDesc[$k] = str_replace('*', '', $word);
			}
		}
	}
}
