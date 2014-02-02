if (!window.VK) window.VK = {};
if (!VK.Share) {
  VK.Share = {
    _popups: [],
    _gens: [],
    _base_domain: '',
    _ge: function(id) {
      return document.getElementById(id);
    },
    button: function(gen, but, index) {
      if (!gen) gen = {};
      if (gen === gen.toString()) gen = {url: gen.toString()};
      if (!gen.url) gen.url = VK.Share._loc;

      if (!but) but = {type: 'round'};
      if (but === but.toString()) but = {type: 'round', text: but};
      if (!but.text) but.text = 'Сохранить';

      var old = true, count_style = 'display: none', count_width = 22;
      if (index === undefined) {
        gen.count = 0;
        gen.shared = (but.type == 'button' || but.type == 'round') ? false : true;
        this._gens.push(gen);
        this._popups.push(false);
        index = this._popups.length - 1;
        old = false;
      } else {
        if ((gen.count = this._gens[index].count) && (but.type == 'button' || but.type == 'round')) {
          count_style = '';
          count_width = 29;
        }
        gen.shared = this._gens[index].shared;
        this._gens[index] = gen;
      }

      var head = document.getElementsByTagName('head')[0];
      if (!this._base_domain) {
        for (var elem = head.firstChild; elem; elem = elem.nextSibling) {
          var m;
          if (elem.tagName && elem.tagName.toLowerCase() == 'script' && (m = elem.src.match(/(http:\/\/(?:[a-z0-9_\-\.]*\.)?(?:vk\.com|vkontakte\.ru)\/)js\/api\/share\.js(?:\?|$)/))) {
            this._base_domain = m[1];
          }
        }
      }
      if (!this._base_domain) {
        this._base_domain = 'http://vkontakte.ru/';
      }
      if (!old && (but.type == 'button' || but.type == 'round')) {
        var elem = document.createElement('script');
        elem.src = this._base_domain + 'share.php?act=count&index=' + index + '&url=' + encodeURIComponent(gen.url);
        head.appendChild(elem);
      }
      if (but.type == 'button' || but.type == 'button_nocount') {
        return '<table cellspacing="0" cellpadding="0" id="vkshare' + index + '" onmouseover="VK.Share.change(1, ' + index + ');" onmouseout="VK.Share.change(0, ' + index + ');" onmousedown="VK.Share.change(2, ' + index + ');" onmouseup="VK.Share.change(1, ' + index + ');" onclick="VK.Share.click(' + index + ');" style="width: auto; cursor: pointer; border: 0px;"><tr style="line-height: normal;"><td></td>' +
               '<td style="vertical-align: middle;"><div style="border: 1px solid #3b6798;"><div style="border: 1px solid #5c82ab; border-top-color: #7e9cbc; background-color: #6d8fb3; color: #fff; text-shadow: 0px 1px #45688E; height: 15px; padding: 2px 4px 0px 6px; font-size: 10px; font-family: tahoma;">' + but.text + '</div></div></td>' +
               '<td style="vertical-align: middle;"><div style="background: url(http://vk.com/images/btns.png) 0px 0px no-repeat; width:' + count_width + 'px; height: 21px"></div></td>' +
               '<td style="vertical-align: middle;"><div style="border: 1px solid #a2b9d3; border-left: 0px; background-color: #dee6f1; height: 15px; padding: 2px 6px 0px 4px; font-size: 10px; font-weight: bold; font-family: tahoma;' + count_style + '">' + gen.count + '</div></td>' +
               '</tr></table>';
      } else if (but.type == 'round' || but.type == 'round_nocount') {
        return '<table cellspacing="0" cellpadding="0" id="vkshare' + index + '" onmouseover="VK.Share.change(1, ' + index + ');" onmouseout="VK.Share.change(0, ' + index + ');" onmousedown="VK.Share.change(2, ' + index + ');" onmouseup="VK.Share.change(1, ' + index + ');" onclick="VK.Share.click(' + index + ');" style="width: auto; cursor: pointer; border: 0px;"><tr style="line-height: normal;">' +
               '<td style="vertical-align: middle;"><div style="height: 21px; width: 2px; background: url(http://vk.com/images/btns.png) no-repeat -21px -42px;"></div></td>' +
               '<td style="vertical-align: middle;"><div style="border: 1px solid #3b6798; border-left: 0px;"><div style="border: 1px solid #5c82ab; border-left: 0px; border-top-color: #7e9cbc; background-color: #6d8fb3; color: #fff; text-shadow: 0px 1px #45688E; height: 15px; padding: 2px 4px 0px 6px; font-family: tahoma; font-size: 10px;">' + but.text + '</div></div></td>' +
               '<td style="vertical-align: middle;"><div style="background: url(http://vk.com/images/btns.png) 0px -21px no-repeat; width:' + count_width + 'px; height: 21px"></div></td>' +
               '<td style="vertical-align: middle;"><div style="border: 1px solid #a2b9d3; border-width: 1px 0px; background-color: #dee6f1; height: 15px; padding: 2px 6px 0px 4px; font-size: 10px; font-weight: bold; font-family: tahoma;' + count_style + '">' + gen.count + '</div></td>' +
               '<td style="vertical-align: middle;"><div style="background: url(http://vk.com/images/btns.png) -27px -42px; width: 2px; height: 21px;' + count_style + '"></div></td>' +
               '</tr></table>';
      } else if (but.type == 'link') {
        return '<table style="width: auto; cursor: pointer; line-height: normal;" onmouseover="this.rows[0].cells[1].firstChild.style.textDecoration=\'underline\'" onmouseout="this.rows[0].cells[1].firstChild.style.textDecoration=\'none\'" onclick="VK.Share.click(' + index + ')" cellspacing="0" cellpadding="0"><tr style="line-height: normal;">' +
               '<td style="vertical-align: middle;"><img src="http://vk.com/images/vk16.png" style="vertical-align: middle;"/></td>' +
               '<td style="vertical-align: middle;"><span style="padding-left: 5px; color: #2B587A; font-family: tahoma; font-size: 11px;">' + but.text + '</span></td>' +
               '</tr></table>';
      } else if (but.type == 'link_noicon') {
        return '<span style="cursor: pointer; font-family: tahoma; font-size: 11px; color: #2B587A; line-height: normal;" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'" onclick="VK.Share.click(' + index + ');">' + but.text + '</span>';
      } else {
        return '<span style="cursor: pointer" onclick="VK.Share.click(' + index + ');">' + but.text + '</span>';
      } 
    },
    change: function(state, index) {
      var row = this._ge('vkshare' + index).rows[0];
      var elem = row.cells[1].firstChild.firstChild;
      if (state == 0) {
        elem.style.backgroundColor = '#6d8fb3';
        elem.style.borderTopColor = '#7e9cbc';
        elem.style.borderLeftColor = elem.style.borderRightColor = elem.style.borderBottomColor = '#5c82ab';
      } else if (state == 1) {
        elem.style.backgroundColor = '#84a1bf';
        elem.style.borderTopColor = '#92acc7';
        elem.style.borderLeftColor = elem.style.borderRightColor = elem.style.borderBottomColor = '#7293b7';
      } else if (state == 2) {
        elem.style.backgroundColor = '#6688ad';
        elem.style.borderBottomColor = '#7495b8';
        elem.style.borderLeftColor = elem.style.borderRightColor = elem.style.borderTopColor = '#51779f';
      }
      var left = row.cells[0].firstChild;
      if (left) {
        if (state == 0) {
          left.style.backgroundPosition = '-21px -42px';
        } else if (state == 1) {
          left.style.backgroundPosition = '-23px -42px';
        } else if (state == 2) {
          left.style.backgroundPosition = '-25px -42px';
        }
      }
    },
    click: function(index) {
      var details = this._gens[index];
      if (!details.shared) {
        VK.Share.count(index, details.count + 1);
        details.shared = true;
      }
      var undefined;
      if (details.noparse === undefined) {
        details.noparse = details.title && details.description && details.image;
      }
      details.noparse = details.noparse ? 1 : 0;

      var params = {url: details.url};
      var fields = ['title', 'description', 'image', 'noparse'];
      for (var i = 0; i < fields.length; ++i) {
        if (details[fields[i]]) {
          params[fields[i]] = details[fields[i]];
        }
      }

      var popupName = '_blank';
      var width = 554;
      var height = 349;
      var left = (screen.width - width) / 2;
      var top = (screen.height - height) / 2;
      var url = this._base_domain + 'share.php';
      var popupParams = 'scrollbars=0, resizable=1, menubar=0, left=' + left + ', top=' + top + ', width=' + width + ', height=' + height + ', toolbar=0, status=0';
      var popup = false;
      try {
        var doc_dom = '', loc_hos = '';
        try {
          doc_dom = document.domain;
          loc_hos = location.host;
        } catch (e) {
        }
        if (doc_dom != loc_hos) {
          var ua = navigator.userAgent.toLowerCase();
          if (!/opera/i.test(ua) && /msie/i.test(ua)) {
            throw 'wont work';
          }
        }
        popup = this._popups[index] = window.open('', popupName, popupParams);
        var text = '<form accept-charset="UTF-8" action="' + url + '" method="POST" id="share_form">';
        for (var i in params) {
          text += '<input type="hidden" name="' + i + '" value="' + params[i].toString().replace(/"/g, '&myquot;').replace(/&quot/ig, '&myquot') + '" />';
        }
        text += '</form>';
        text += '<script type="text/javascript">document.getElementById("share_form").submit()</script>';

        text = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' +
               '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' +
               '<head><meta http-equiv="content-type" content="text/html; charset=windows-1251" /></head>' +
               '<body>' + text + '</body></html>';
        popup.document.write(text);
        popup.blur();
        popup.focus();
      } catch (e) { // ie with changed domain.
        try {
          if (popup) {
            popup.close();
          }
          url += '?';
          for (var i in params) {
            url += encodeURIComponent(i) + '=' + encodeURIComponent(params[i]) + '&';
          }
          popup = this._popups[index] = window.open(url, popupName, popupParams);
          popup.focus();
        } catch (e) {
        }
      }
    },
    count: function(index, count) {
      this._gens[index].count = count;
      var elem = this._ge('vkshare' + index);
      if (elem) {
        var row = elem.rows[0];
        if (count) {
          row.cells[3].firstChild.innerHTML = count;
          row.cells[2].firstChild.style.width = '29px';
          row.cells[3].firstChild.style.display = 'block';
          if (row.cells.length > 4) {
            row.cells[4].firstChild.style.display = 'block';
          }
        } else {
          row.cells[2].firstChild.style.width = '22px';
          row.cells[3].firstChild.style.display = 'none';
          if (row.cells.length > 4) {
            row.cells[4].firstChild.style.display = 'none';
          }
        }
      }
    }
  }
  try {
    VK.Share._loc = location.toString();
  } catch(e) {
    VK.Share._loc = 'http://vkontakte.ru/';
  }
}
