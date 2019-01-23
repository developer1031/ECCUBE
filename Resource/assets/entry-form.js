function fnSetClassCategories(form, classcat_id2_selected) {
  var $form = $(form);
  var product_id = $form.find('input[name="add_cart[product_id]"]').val();
  // var $sele1 = $form.find('[name="add_cart[classcategory_id1]"]:checked');
  // var $sele2 = $form.find('[name="add_cart[classcategory_id2]"]:checked');
  var $sele1 = $form.find('[name="add_cart[classcategory_id1]"]');
  var $sele2 = $form.find('[name="add_cart[classcategory_id2]"]');
  eccube.setClassCategories($form, product_id, $sele1, $sele2, classcat_id2_selected);
}

function updatePriceTag() {
  var $form = $('#form1');
  // var cc1Id = $form.find('[name="add_cart[classcategory_id1]"]:input:checked').val();
  var cc1Id = $form.find('[name="add_cart[classcategory_id1]"]:input').val();
  var cc2Id = $form.find('[name="add_cart[classcategory_id2]"]:input').val();
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

function saveFormData($form) {
  Cookies.set('efo', $form.serialize(), {expires: 7});
}

function loadFormData() {
  return Cookies.get('efo');
}

function suggestAddress() {
  AjaxZip3.zip2addr('nonmember[zip][zip01]', 'nonmember[zip][zip02]', 'nonmember[address][pref]', 'nonmember[address][addr01]');
}

/**
 * 規格2のプルダウンを設定する.
 */
eccube.setClassCategories = function ($form, product_id, $sele1, $sele2, selected_id2) {
  if ($sele1 && $sele1.length) {
    var classcat_id1 = $sele1.val() ? $sele1.val() : '';
    if ($sele2 && $sele2.length) {
      // 規格2の選択肢をクリア
      $sele2.children().remove();

      var classcat2;

      // 商品一覧時
      if (eccube.hasOwnProperty('productsClassCategories')) {
        classcat2 = eccube.productsClassCategories[product_id][classcat_id1];
      }
      // 詳細表示時
      else {
        classcat2 = eccube.classCategories[classcat_id1];
      }

      // 規格2の要素を設定
      selected_id2 = String(selected_id2);
      for (var key in classcat2) {
        if (classcat2.hasOwnProperty(key)) {
          var id = String(classcat2[key].classcategory_id2);
          var name = classcat2[key].name;
          var option = $('<option />').val(id ? id : '').text(name);
          if (id === selected_id2) {
            option.attr('selected', true);
          }
          if (id) {
            $sele2.append(option);
          }
        }
      }
      eccube.checkStock($form, product_id, $sele1.val() ? $sele1.val() : '__unselected2',
        $sele2.val() ? $sele2.val() : '');
    }
  }
};

/**
 * 規格の選択状態に応じて, フィールドを設定する.
 */
eccube.checkStock = function ($form, product_id, classcat_id1, classcat_id2) {

  classcat_id2 = classcat_id2 ? classcat_id2 : '';

  var classcat2;

  // 商品一覧時
  if (eccube.hasOwnProperty('productsClassCategories')) {
    classcat2 = eccube.productsClassCategories[product_id][classcat_id1]['#' + classcat_id2];
  }
  // 詳細表示時
  else {
    classcat2 = eccube.classCategories[classcat_id1]['#' + classcat_id2];
  }

  // 商品コード
  var $product_code_default = $form.find('[id^=product_code_default]');
  var $product_code_dynamic = $form.find('[id^=product_code_dynamic]');
  if (classcat2 && typeof classcat2.product_code !== 'undefined') {
    $product_code_default.hide();
    $product_code_dynamic.show();
    $product_code_dynamic.text(classcat2.product_code);
  } else {
    $product_code_default.show();
    $product_code_dynamic.hide();
  }

  // 在庫(品切れ)
  var $cartbtn_default = $form.find('[id^=cartbtn_default]');
  var $cartbtn_dynamic = $form.find('[id^=cartbtn_dynamic]');
  if (classcat2 && classcat2.stock_find === false) {

    $cartbtn_dynamic.text('申し訳ございませんが、只今品切れ中です。').show();
    $cartbtn_default.hide();
  } else {
    $cartbtn_dynamic.hide();
    $cartbtn_default.show();
  }

  // 通常価格
  var $price01_default = $form.find('[id^=price01_default]');
  var $price01_dynamic = $form.find('[id^=price01_dynamic]');
  if (classcat2 && typeof classcat2.price01 !== 'undefined' && String(classcat2.price01).length >= 1) {

    $price01_dynamic.text(classcat2.price01).show();
    $price01_default.hide();
  } else {
    $price01_dynamic.hide();
    $price01_default.show();
  }

  // 販売価格
  var $price02_default = $form.find('[id^=price02_default]');
  var $price02_dynamic = $form.find('[id^=price02_dynamic]');
  if (classcat2 && typeof classcat2.price02 !== 'undefined' && String(classcat2.price02).length >= 1) {

    $price02_dynamic.text(classcat2.price02).show();
    $price02_default.hide();
  } else {
    $price02_dynamic.hide();
    $price02_default.show();
  }

  // ポイント
  var $point_default = $form.find('[id^=point_default]');
  var $point_dynamic = $form.find('[id^=point_dynamic]');
  if (classcat2 && typeof classcat2.point !== 'undefined' && String(classcat2.point).length >= 1) {

    $point_dynamic.text(classcat2.point).show();
    $point_default.hide();
  } else {
    $point_dynamic.hide();
    $point_default.show();
  }

  // 商品規格
  var $product_class_id_dynamic = $form.find('[id^=product_class_id]');

  if ($product_class_id_dynamic.length == 0) {
    $product_class_id_dynamic = $form.find('[name="add_cart[product_class_id]"]');
  }

  if (classcat2 && typeof classcat2.product_class_id !== 'undefined' && String(classcat2.product_class_id).length >= 1) {
    $product_class_id_dynamic.val(classcat2.product_class_id);
  } else {
    $product_class_id_dynamic.val('');
  }
};

$(function () {
  var $form = $('#form1');
  var $name01 = $('[name="nonmember[name][name01]"]:input');
  var $name02 = $('[name="nonmember[name][name02]"]:input');
  var $kana01 = $('[name="nonmember[kana][kana01]"]:input');
  var $kana02 = $('[name="nonmember[kana][kana02]"]:input');
  var $zip01 = $('[name="nonmember[zip][zip01]"]:input');
  var $zip02 = $('[name="nonmember[zip][zip02]"]:input');
  var $pref = $('[name="nonmember[address][pref]"]:input');
  var $addr01 = $('[name="nonmember[address][addr01]"]:input');
  var $addr02 = $('[name="nonmember[address][addr02]"]:input');
  var $tel01 = $('[name="nonmember[tel][tel01]"]:input');
  var $tel02 = $('[name="nonmember[tel][tel02]"]:input');
  var $tel03 = $('[name="nonmember[tel][tel03]"]:input');
  var $fax01 = $('[name="nonmember[fax][fax01]"]:input');
  var $fax02 = $('[name="nonmember[fax][fax02]"]:input');
  var $fax03 = $('[name="nonmember[fax][fax03]"]:input');
  var $emailFirst = $('[name="nonmember[email][first]"]:input');
  var $emailSecond = $('[name="nonmember[email][second]"]:input');
  var $passwordFirst = $('[name="nonmember[password][first]"]:input');
  var $passwordSecond = $('[name="nonmember[password][second]"]:input');

  $name01.addClass('validate[required]');
  $name02.addClass('validate[required]');
  $kana01.addClass('validate[required]');
  $kana02.addClass('validate[required]');
  $zip01.addClass('validate[required,custom[onlyNumberSp]]');
  $zip02.addClass('validate[required,custom[onlyNumberSp]]');
  $pref.addClass('validate[required]');
  $addr01.addClass('validate[required]');
  $tel01.addClass('validate[required,custom[onlyNumberSp]]');
  $tel02.addClass('validate[required,custom[onlyNumberSp]]');
  $tel03.addClass('validate[required,custom[onlyNumberSp]]');
  $fax01.addClass('validate[custom[onlyNumberSp]]');
  $fax02.addClass('validate[custom[onlyNumberSp]]');
  $fax03.addClass('validate[custom[onlyNumberSp]]');
  $emailFirst.addClass('validate[required,custom[email]]');
  $emailSecond.addClass('validate[required,custom[email],equals[nonmember_email_first]]');
  $passwordFirst.addClass('validate[condRequired[entry],custom[onlyPassword],minSize[8],maxSize[32]]');
  $passwordSecond.addClass('validate[equals[nonmember_password_first]]');

  // 規格1選択時
  $('[name="add_cart[classcategory_id1]"]:input')
    .change(function () {
      var $form = $(this).parents('form');
      var product_id = $form.find('[name="add_cart[product_id]"]:input').val();
      var $sele1 = $(this);
      var $sele2 = $form.find('[name="add_cart[classcategory_id2]"]:input');

      // 規格1のみの場合
      if (!$sele2.length) {
        eccube.checkStock($form, product_id, $sele1.val(), null);
        // 規格2ありの場合
      } else {
        eccube.setClassCategories($form, product_id, $sele1, $sele2, selectedClassCategoryId2);
      }
    });

  // 規格2選択時
  $('[name="add_cart[classcategory_id2]"]:input')
    .change(function () {
      var $form = $(this).parents('form');
      var product_id = $form.find('[name="add_cart[product_id]"]:input').val();
      var $sele1 = $form.find('[name="add_cart[classcategory_id1]"]:input:checked');
      var $sele2 = $(this);
      eccube.checkStock($form, product_id, $sele1.val(), $sele2.val());
    });

  $('#zip-search')
    .click(function (e) {
      e.preventDefault();
      suggestAddress();
    });

  $form
    .validationEngine({
      promptPosition: 'topLeft',
      showOneMessage: true
    });

  $('[name="add_cart[classcategory_id1]"]:input, [name="add_cart[classcategory_id2]"]:input')
    .change(function () {
      updatePriceTag();
    });

  $('[name="add_cart[classcategory_id1]"]:checked, [name="add_cart[classcategory_id2]"]:checked')
    .trigger('change');

  $.fn.autoKana('[name="nonmember[name][name01]"]:input', '[name="nonmember[kana][kana01]"]:input', {katakana: true});
  $.fn.autoKana('[name="nonmember[name][name02]"]:input', '[name="nonmember[kana][kana02]"]:input', {katakana: true});

  $name01
    .blur(function () {
      $kana01
        .validationEngine('hideAll')
        .validationEngine('validate');
    });

  $name02
    .blur(function () {
      $kana02
        .validationEngine('hideAll')
        .validationEngine('validate');
    });

  $zip01
    .on('keyup', function () {
      if (this.value.length == 3) {
        $zip02.focus();
      }
    });

  $zip02
    .on('keyup', function () {
      if (this.value.length == 4) {
        suggestAddress();

        setTimeout(function () {
          if ($pref.val() === '' || $pref.val() === undefined) {
            $pref.focus();
          } else if ($addr01.val() === '') {
            $addr01.focus();
          } else if ($addr02.val() === '') {
            $addr02.focus();
          } else {
            $tel01.focus();
            $tel01.select();
          }
        }, 500);

        $pref.validationEngine('hideAll').validationEngine('validate');
        $addr01.validationEngine('hideAll').validationEngine('validate');
        $addr02.validationEngine('hideAll').validationEngine('validate');
      }
    });

  $tel01
    .on('keyup', function () {
      if (this.value == '090' || this.value == '080' || this.value == '070' || this.value == '050' || this.value.length == 4) {
        $tel02.focus();
      }
    });

  $tel02
    .on('keyup', function () {
      if (this.value.length == 4) {
        $tel03.focus();
      }
    });

  $tel03
    .on('keyup', function () {
      if (this.value.length == 4) {
        $emailFirst.focus();
      }
    });

  var $deliveryTime = $('select[name="shippings[0][deliveryTime]"]');
  $('select[name="shippings[0][delivery]"]').change(function () {
    var deliveryId = $(this).val();
    var deliveryTimes = groupedDeliveryTimes[deliveryId];
    $deliveryTime.empty();
    if (deliveryTimes) {
      $deliveryTime.show();
      deliveryTimes.map(function (deliveryTime) {
        $('<option />').val(deliveryTime.id).text(deliveryTime.label).appendTo($deliveryTime);
      });
    } else {
      $deliveryTime.hide();
    }
  });

  $('#add_cart_classcategory_id1 > option').each(function () {
    var $option = $(this);
    var value = $option.val();
    if (value == '__unselected') {
      $option.remove();
    }
  });

  $('#add_cart_classcategory_id1').trigger('change');
  var oldFormData = loadFormData();
  var $fillCustomerButton = $('#fill-customer-button').click(function (e) {
    e.preventDefault();
    var $token = $form.find('input[name=_token]').first();
    var token = $token.val();
    $form.deserialize(oldFormData);
    $token.val(token);
    $('#add_cart_classcategory_id1').trigger('change');
    $form.validationEngine('validate');
  });

  if (oldFormData) {
    $fillCustomerButton.show();
  } else {
    $fillCustomerButton.hide();
  }

  var formHasError = false;

  $form.on('jqv.form.result', function (event, errorFound) {
    formHasError = errorFound;
  });

  $(window).unload(function () {
    $($form.find('input[name=_token]')).remove();
    saveFormData($form);
  });

  $('a[href^=#]').click(function () {
    var speed = 500;
    var href = $(this).attr("href");
    var target = $(href == "#" || href == "" ? 'html' : href);
    var position = target.offset().top;
    $("html, body").animate({scrollTop: position}, speed, "swing");
    return false;
  });

  $('.carousel').slick({
    infinite: false,
    speed: 300,
    prevArrow: '<button type="button" class="slick-prev"><span class="angle-circle"><svg class="cb cb-angle-right"><use xlink:href="#cb-angle-right" /></svg></span></button>',
    nextArrow: '<button type="button" class="slick-next"><span class="angle-circle"><svg class="cb cb-angle-right"><use xlink:href="#cb-angle-right" /></svg></span></button>',
    slidesToShow: 4,
    slidesToScroll: 4,
    responsive: [
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3
        }
      }
    ]
  });

  $('.slides').slick({
    dots: true,
    arrows: false,
    speed: 300,
    customPaging: function (slider, i) {
      return '<button class="thumbnail">' + $(slider.$slides[i]).find('img').prop('outerHTML') + '</button>';
    }
  });

  $('#detail_image_box__slides')
    .on('click', 'button', function (e) {
      e.preventDefault();
    });

  var $remindForm = $('#remind_form');

  if (loadFormData()) {
    $remindForm.show();
  } else {
    $remindForm.hide();
  }
});
