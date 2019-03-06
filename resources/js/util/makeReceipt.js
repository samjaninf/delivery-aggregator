const { formatTime, formatDate } = require("./formatTime");

const title = s => `<medium2><normal><left>${s}<br>`;
const subtitle = s => `<medium1><normal><left>${s}<br>`;
const euro = x => `${(+x).toFixed(2)} E`;

const products = order => {
  const items = order.items.map(i => {
    const title = subtitle(`${i.quantity} x ${i.name}`);
    const addons = Object.keys(i.meta)
      .map(k => `<small>  ${k}<br><small>  ${i.meta[k]}<br>`)
      .join("");
    const price = `<small>  ${euro(i.total)}`;
    return `${title}${addons}${price}`;
  });

  const shipping = `${subtitle("Spedizione")}  ${euro(order.shipping)}`;

  return `${title("Prodotti")}${[...items, shipping].join("<br>")}`;
};

const orderData = order => {
  const {
    number,
    total,
    payment_method,
    delivery_date,
    delivery_date_end,
    coupons,
    notes
  } = order;

  const toPay = payment_method === "cod" ? "da pagare" : "già pagato";
  const notesData = notes ? [`Note: ${notes}`] : [];
  const couponsData =
    Array.isArray(coupons) && coupons.length > 0
      ? coupons.map(
          ({ code, discount }) => `Coupon: ${code} (${euro(discount)})`
        )
      : [];

  const data = [
    `Totale: ${euro(total)} (${toPay})`,
    `Data: ${formatDate(delivery_date)}`,
    `Orario: ${formatTime(delivery_date)} ~ ${formatTime(delivery_date_end)}`,
    ...couponsData,
    ...notesData
  ]
    .map(s => `<small>${s}`)
    .join("<br>");

  return `${title(`Ordine n. ${number}`)}${data}`;
};

const clientData = order => {
  const { first_name, last_name, address, city, phone } = order;

  const data = [
    `Nome: ${first_name} ${last_name}`,
    `Indirizzo: ${address}, ${city}`,
    `Telefono: ${phone}`
  ]
    .map(s => `<small>${s}`)
    .join("<br>");

  return `${title("Cliente")}${data}`;
};

const makeReceipt = (store, order) => {
  const storeName = store.name || "";
  const header = `<center><big><bold>${storeName}<br><br>`;

  const sections = [orderData, products, clientData];

  const footer = "<cut>";
  const text = `${header}${sections
    .map(s => `${s(order)}<br>`)
    .join("<br>")}${footer}`;
  return text;
};

module.exports = makeReceipt;
