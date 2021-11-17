<?php 

extract($_REQUEST);

include '../head/jackus.php';

	if($selected_PRDTID !='' && $selected_CARTID !=''){
		$check_selecteditem = sqlQUERY_LABEL("select `item_tax`,`od_id`, `item_tax_type`, `pd_id`, `variant_id`, `od_colorid`, `od_size_id`, `od_qty`, `od_price`, `item_tax1`, `od_session`, `item_tax2` from js_shop_order_item where user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'") or die(sqlERROR_LABEL());

		while($collect_selecteditem = sqlFETCHARRAY_LABEL($check_selecteditem)) {

			$quantity = $collect_selecteditem['od_qty'];
			$od_id = $collect_selecteditem['od_id'];
			$variant_id = $collect_selecteditem['variant_id'];
			$total_price = $collect_selecteditem['od_price'];
			$item_tax1 = $collect_selecteditem['item_tax1'];
			$item_tax2 = $collect_selecteditem['item_tax2'];
			$item_tax = $collect_selecteditem['item_tax'];
			$od_session = $collect_selecteditem['od_session'];
			$item_tax_type = $collect_selecteditem['item_tax_type'];
			if($quantity > 1){
			$old_total_price = (($item_tax1 + $item_tax2 + $total_price)/$quantity);
			} else {
			$old_total_price = $item_tax1 + $item_tax2 + $total_price;
			}
			$newtotal_price = $old_total_price * $selected_PRDTQTY;
			
			$PRDT_CODE = getPRDT_CODE($selected_PRDTID, $variant_id, 'get_prdt_code');
			
			$list_producttype_data = sqlQUERY_LABEL("select `productopeningstock`,`productavailablestock` from js_product where productsku = '$PRDT_CODE'");
			$count_producttype_list = sqlNUMOFROW_LABEL($list_producttype_data);
				if($count_producttype_list > 0){
		//echo 'A<br>';
					while($get_product_data = sqlFETCHARRAY_LABEL($list_producttype_data)) {
						$prdt_opening_qty = $get_product_data['productopeningstock'];
						$prdt_available_qty = $get_product_data['productavailablestock'];
					}
				} else {
					
					$list_producttype_data_variant = sqlQUERY_LABEL("select `variant_available_stock`,`variant_opening_stock` from js_productvariants where variant_code = '$PRDT_CODE' and deleted='0'");
					//$count_producttype_list = sqlNUMOFROW_LABEL($list_producttype_data_variant);
					while($get_product_data_variant = sqlFETCHARRAY_LABEL($list_producttype_data_variant)) {
						$prdt_opening_qty = $get_product_data_variant['variant_available_stock'];
						$prdt_available_qty = $get_product_data_variant['variant_opening_stock'];
					}
				}
				if($item_tax_type == 'Y') {
					$taxsplit_price = ($newtotal_price * ($item_tax/100))/2;
					$newtotal_price = ($newtotal_price - ($taxsplit_price * 2));
				} else {
					$taxsplit_price = (($newtotal_price * ($item_tax/100))/2);
					$newtotal_price = $newtotal_price;
				}
		}
		$sql_query_token = sqlQUERY_LABEL("SELECT `prdt_id`,`offers_id` FROM `js_offers_items` where `prdt_id`='$selected_PRDTID' and deleted = '0' and status = '2'") or die("#STATUS-SELECT: Getting Status: ".sqlERROR_LABEL());
			while($set_sql_query_token = sqlFETCHARRAY_LABEL($sql_query_token)){
				$productID_token = $set_sql_query_token['prdt_id'];
				$offers_id = $set_sql_query_token['offers_id'];
			 }
		 $offer_type =  getSINGLEDBVALUE('offers_type', " offers_id ='$offers_id' and deleted = '0' and status = '2'", 'js_offers', 'label');
							 
		    // echo $cart_id ; exit();
			
		if($selected_PRDTQTY <= $prdt_available_qty && $offers_id !='' ){
		sqlQUERY_LABEL("INSERT into `js_shop_order_item` (`createdby`, `pd_id`, `variant_id`, `user_id`, `od_session`, `od_qty`, `od_price`, `item_tax_type`, `item_tax`, `item_tax1`, `item_tax2`,`status`,`offer_id`,`offer_type`,`redeem_offer`) VALUES ('$logged_user_id','$selected_PRDTID', '$variant_id', '$logged_user_id', '$od_session', '1', '$total_price', '$item_tax_type', '$item_tax', '$item_tax1', '$item_tax2','1','$offers_id','$offer_type','$redeem_offer')") or die("#1 Unable to add Quick Item:" . sqlERROR_LABEL());
		$redeem_offer_cart_id = sqlINSERTID_LABEL();
			if($redeem_offer=='2'){
			sqlQUERY_LABEL("UPDATE `js_shop_order_item` SET `redeem_offer` = '1', `redeem_offer_cart_id` = '$redeem_offer_cart_id' WHERE  cart_id IN ($cart_id)") or die(sqlERROR_LABEL());
			
				
			}
$data = array('status' => 'Success', 'msg' => 'INSERT');			
		}
		elseif($selected_PRDTQTY <= $prdt_available_qty){
			
			sqlQUERY_LABEL("UPDATE `js_shop_order_item` SET `od_price` = '$newtotal_price', `od_qty`='$selected_PRDTQTY', `item_tax1`='$taxsplit_price', `item_tax2`='$taxsplit_price' WHERE createdby = '$logged_user_id' and user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'") or die(sqlERROR_LABEL());
			$message = "Cart updated...!!!";
			$data = array('status' => 'Success', 'msg' => 'INSERT');
		} else {
		//echo $selected_PRDTID;
			if($variant_id !='0'){
				$get_variant_name = ' ('.getVARIANT_CODE($variant_id,'variant_name_from_variant_ID').')';
			} else {
				$get_variant_name = '';
			}
			$get_prdt_name = getPRDT_CODE($selected_PRDTID,'','get_prdt_name');
			$err .= "Stock not availabe for ".$get_prdt_name.$get_variant_name.'<br />';
			$data = array('status' => 'Error_Stock', 'msg' => 'NoStock');
		}
	} else {
		$data = array('status' => 'Error', 'msg' => 'Updated');
	}

header('Content-Type: application/json');
echo json_encode($data);
?>