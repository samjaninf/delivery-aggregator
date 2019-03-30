import { formatTime, formatDate } from "./formatTime";

const title = s => `<medium2><normal><left>${s}<br>`;
const subtitle = s => `<medium1><normal><left>${s}<br>`;
const euro = x => `${(+x).toFixed(2)} EUR`;
const replaceHtmlEntities = s => s.replace(/(&euro;|€)/, "EUR");

const products = order => {
  const items = order.items.map(i => {
    const heading = subtitle(`${i.quantity} x ${i.name}`);
    const addons = Object.keys(i.meta)
      .map(
        k => `<small>  ${replaceHtmlEntities(k)}<br><small>  ${i.meta[k]}<br>`
      )
      .join("");
    const price = `<small>  ${euro(i.total)}`;
    return `${heading}${addons}${price}`;
  });

  const shipping = `${subtitle("Spedizione")}<small>  ${euro(order.shipping)}`;

  return `${title("Prodotti")}${[...items, shipping].join("<br>")}`;
};

const orderData = order => {
  const {
    number,
    total,
    paymentMethod,
    deliveryDate,
    deliveryDateEnd,
    coupons,
    notes
  } = order;

  const toPay = paymentMethod === "cod" ? "da pagare" : "già pagato";
  const notesData = notes ? [`Note: ${notes}`] : [];
  const couponsData =
    Array.isArray(coupons) && coupons.length > 0
      ? coupons.map(
          ({ code, discount }) => `* Coupon: ${code} (${euro(discount)})`
        )
      : [];

  const data = [
    `Totale: ${euro(total)} (${toPay})`,
    `Data: ${formatDate(deliveryDate)}`,
    `Orario: ${formatTime(deliveryDate)} ~ ${formatTime(deliveryDateEnd)}`,
    ...couponsData,
    ...notesData
  ]
    .map(s => `<small>${s}`)
    .join("<br>");

  return `${title(`Ordine n. ${number}`)}${data}`;
};

const clientData = order => {
  const { firstName, lastName, address, city, phone } = order;

  const data = [
    `Nome: ${firstName} ${lastName}`,
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

export default makeReceipt;
