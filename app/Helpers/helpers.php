<?php

use App\Models\PurchaseRequest;

if (!function_exists('make_purchase_request_no')) {
  function make_purchase_request_no()
  {
    $record = PurchaseRequest::latest('id')->first();

    $prefix = "PRA-";

    if ($record === null or $record === "") {
      $requestNo = $prefix . date('ym') . '-0001';
    } else {
      $expNum = explode('-', $record->code);
      if (date('ym') === $expNum[1]) {
        $number = ($expNum[2] + 1);
        $requestNo = $prefix . date('ym') . '-' . str_pad($number, 4, 0, STR_PAD_LEFT);
      } else {
        $requestNo = $prefix . date('ym') . '-0001';
      }
    }

    return $requestNo;
  }
}
