function suggestAddress() {
  AjaxZip3.zip2addr('entry[zip][zip01]', 'entry[zip][zip02]', 'entry[address][pref]', 'entry[address][addr01]');
}

$(function () {
  var $form = $('form');
  var $name01 = $('[name="entry[name][name01]"]:input');
  var $name02 = $('[name="entry[name][name02]"]:input');
  var $kana01 = $('[name="entry[kana][kana01]"]:input');
  var $kana02 = $('[name="entry[kana][kana02]"]:input');
  var $zip01 = $('[name="entry[zip][zip01]"]:input');
  var $zip02 = $('[name="entry[zip][zip02]"]:input');
  var $pref = $('[name="entry[address][pref]"]:input');
  var $addr01 = $('[name="entry[address][addr01]"]:input');
  var $addr02 = $('[name="entry[address][addr02]"]:input');
  var $tel01 = $('[name="entry[tel][tel01]"]:input');
  var $tel02 = $('[name="entry[tel][tel02]"]:input');
  var $tel03 = $('[name="entry[tel][tel03]"]:input');
  var $fax01 = $('[name="entry[fax][fax01]"]:input');
  var $fax02 = $('[name="entry[fax][fax02]"]:input');
  var $fax03 = $('[name="entry[fax][fax03]"]:input');
  var $emailFirst = $('[name="entry[email][first]"]:input');
  var $emailSecond = $('[name="entry[email][second]"]:input');
  var $passwordFirst = $('[name="entry[password][first]"]:input');
  var $passwordSecond = $('[name="entry[password][second]"]:input');

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
  $emailSecond.addClass('validate[required,custom[email],equals[entry_email_first]]');
  $passwordFirst.addClass('validate[condRequired[entry],custom[onlyPassword],minSize[8],maxSize[32]]');
  $passwordSecond.addClass('validate[equals[entry_password_first]]');

  $form
    .validationEngine({
      promptPosition: 'topLeft',
      showOneMessage: true
    });

  $.fn.autoKana('[name="entry[name][name01]"]:input', '[name="entry[kana][kana01]"]:input', {katakana: true});
  $.fn.autoKana('[name="entry[name][name02]"]:input', '[name="entry[kana][kana02]"]:input', {katakana: true});

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
});
