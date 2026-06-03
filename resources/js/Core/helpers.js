export const hasValueWithZero = value => {
  return value === '0' || value === 0 || value;
};

export const calculateDiscount = (price, discount) => {
  let discount_amount = 0;
  if (discount.indexOf('%') !== -1) {
    var pds = discount.split('%');
    if (!isNaN(pds[0])) {
      discount_amount = Number((price * Number(pds[0])) / 100);
    }
  } else {
    discount_amount = Number(discount);
  }
  return number_format(discount_amount);
};

export const calculateTax = (price, taxes, method) => {
  let amount = 0;
  if (taxes && taxes.length) {
    for (const tax of taxes) {
      if (tax.fixed != 1 && tax.rate != 0) {
        if (method == 'inclusive') {
          amount += Number(number_format((Number(price) * Number(tax.rate)) / (100 + Number(tax.rate)), 2));
        } else {
          amount += Number(number_format((Number(price) * Number(tax.rate)) / 100, 2));
        }
      } else {
        amount += Number(tax.rate);
      }
    }
  }
  return amount;
};

import { usePage } from '@inertiajs/vue3';
export const number_format = (number, decimals, decPoint = '.', thousandsSep = '') => {
  if (decimals === undefined) {
    decimals = usePage().props.settings?.fraction || 0;
  }
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number;
  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
  var sep = typeof thousandsSep === 'undefined' ? ',' : thousandsSep;
  var dec = typeof decPoint === 'undefined' ? '.' : decPoint;
  var s = '';

  var toFixedFix = function (n, prec) {
    if (('' + n).indexOf('e') === -1) {
      return +(Math.round(n + 'e+' + prec) + 'e-' + prec);
    } else {
      var arr = ('' + n).split('e');
      var sig = '';
      if (+arr[1] + prec > 0) {
        sig = '+';
      }
      return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec);
    }
  };

  // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }

  return s.join(dec);
};
