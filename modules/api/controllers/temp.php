<?php 
public function actionList()
{
    $filters = [];
    $client = Yii::$app->meili->connect();
    $index = $client->index('object');
    $queryWord = Yii::$app->request->get('query_word', '');
    $fromDate = Yii::$app->request->get('from_date');
    $toDate = Yii::$app->request->get('to_date');
    $type = (int) Yii::$app->request->get('type', null);
    $amount = (int) Yii::$app->request->get('amount', null);
    $user_auth = null;
    $token = Yii::$app->request->headers->get('Authorization');
    if ($token && preg_match('/^Bearer\s+(.*?)$/', $token, $matches)) {
        $user_auth = $matches[1]; // Extract token
    }

    $guestAmount = (int) Yii::$app->request->get('guest_amount', 1);
    $filters = ['rooms.guest_amount >= ' . $guestAmount];
    $pageSize = 10; // Number of results per page
    $page = (int) Yii::$app->request->get('page', 1); // Get page from request
    $offset = ($page - 1) * $pageSize;

    $searchResults = $index->search($queryWord, [
        'filter' => $filters,
        'sort' => [$priceField . ':asc'],
        'limit' => $pageSize,
        'offset' => $offset
    ]);

    // Process results to add from_price
    $hits = $searchResults->getHits();
    $totalCount = count($hits);

    $pagination = new Pagination([
        'totalCount' => $totalCount,
        'pageSize' => $pageSize,
    ]);

    $arr = [
        'pageSize' => $pagination->pageSize,
        'totalCount' => $searchResults->getEstimatedTotalHits(),
        'page' => (int) $page,
        'data' => $hits,
    ];
    return $arr;
}
?>