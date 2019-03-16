import React from "react";
import { formatTime } from "../util/formatTime";

import {
  FaRegClock,
  FaUser,
  FaMapPin,
  FaPhone,
  FaMoneyBillAlt
} from "react-icons/fa";

const Order = ({
  number,
  delivery_date,
  total,
  first_name,
  last_name,
  phone,
  address
}) => {
  return (
    <div className="card">
      <h4>Ordine #{number}</h4>
      <p>
        <FaRegClock /> {formatTime(delivery_date)}
      </p>
      <p>
        <FaUser /> {first_name} {last_name}
      </p>
      <p>
        <FaMapPin /> {address}
      </p>
      <p>
        <FaPhone /> {phone}
      </p>
      <p>
        <FaMoneyBillAlt /> €{(+total).toFixed(2)} (Contanti)
      </p>
    </div>
  );
};

export default Order;
