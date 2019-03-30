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
  deliveryDate,
  total,
  firstName,
  lastName,
  phone,
  address
}) => {
  return (
    <div className="card">
      <h4>Ordine #{number}</h4>
      <p>
        <FaRegClock /> {formatTime(deliveryDate)}
      </p>
      <p>
        <FaUser /> {firstName} {lastName}
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
