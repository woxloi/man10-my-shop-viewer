const saveAPIKey = (key) => {
  window.localStorage.setItem('API_KEY', key);
};

const loadAPIKey = () => {
  return window.localStorage.getItem('API_KEY');
};

let keyElement, buttonElement, tableElement;

async function getShopList() {
  const key = keyElement.value.trim();
  if (!key) {
    alert('APIキーを入力してください');
    return;
  }

  saveAPIKey(key);

  const form = new FormData();
  form.append('key', key);

  try {
    const response = await fetch('shop.php', {
      method: 'POST',
      body: form
    });

    console.log('HTTP status:', response.status);
    const shopData = await response.json();
    console.log('受け取ったデータ:', shopData);

    if (!response.ok) {
      // エラーメッセージがJSONのerrorにあればそれを表示
      const errorMsg = shopData.error || `HTTPエラー: ${response.status}`;
      throw new Error(errorMsg);
    }

    tableElement.innerHTML = ''; // tbodyをクリア

    for (const shop of shopData) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${shop.name}</td>
        <td>${shop.shopType}</td>
        <td>${shop.icon?.material || ''}</td>
        <td>${shop.itemCount}</td>
        <td>${shop.money}</td>
      `;
      tableElement.appendChild(tr);
    }

  } catch (err) {
    console.error('エラー:', err);
    alert('ショップ一覧の取得に失敗しました。\n' + err.message);
  }
}

// DOMが読み込まれたらイベント登録
window.addEventListener('DOMContentLoaded', () => {
  keyElement = document.getElementById('apiKeyInput');
  buttonElement = document.getElementById('fetchButton');
  tableElement = document.getElementById('shopTableBody');

  keyElement.value = loadAPIKey() || '';
  buttonElement.addEventListener('click', getShopList);
});
