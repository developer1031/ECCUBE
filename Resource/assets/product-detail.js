function updatePriceTag(cc1Id, cc2Id) {
  var cc = eccube.classCategories[cc1Id]['#' + (cc2Id ? cc2Id : '')];
  var $normalPrice = $('.normal_price').empty();
  var $salePrice = $('.sale_price').empty();

  if (cc.price01 != '0') {
    $normalPrice
      .append($('<span class="price01_default"></span>').text('通常価格：￥' + cc.price01))
      .append($('<span class="small"> 税込</span>'));
  }

  if (cc.price02 != '0') {
    $salePrice
      .append($('<span class="price02_default"></span>').text('￥' + cc.price02))
      .append($('<span class="small"> 税込</span>'));
  }
}

$(function () {
  var $form = $('#form1');

  Object.keys(eccube.classCategories).map(function (ccId2) {
    delete eccube.classCategories[ccId2]['#'];
  });

  $form.find('[name="classcategory_id1"]:input > option[value="__unselected"]').remove();

  // 規格1選択時
  $('[name="classcategory_id1"]:input')
    .change(function () {
      var $form = $(this).parents('form');
      var product_id = $form.find('[name="product_id"]:input').val();
      var $sele1 = $(this);
      var $sele2 = $form.find('[name="classcategory_id2"]:input');
      var ccId1 = $sele1.val();
      var ccId2 = $sele2.val();

      // 規格1のみの場合
      if (!$sele2.length) {
        eccube.checkStock($form, product_id, ccId1, null);
        // 規格2ありの場合
      } else {
        eccube.setClassCategories($form, product_id, $sele1, $sele2, ccId2);
      }

      updatePriceTag(ccId1, ccId2);
    })
    .trigger('change');

  // 規格2選択時
  $('[name="classcategory_id2"]:input')
    .change(function () {
      var $form = $(this).parents('form');
      var product_id = $form.find('[name="product_id"]:input').val();
      var $sele1 = $form.find('[name="classcategory_id1"]:input');
      var $sele2 = $(this);
      var ccId1 = $sele1.val();
      var ccId2 = $sele2.val();
      eccube.checkStock($form, product_id, ccId1, ccId2);

      updatePriceTag(ccId1, ccId2);
    });
});
