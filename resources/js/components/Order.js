import React from "react";
import PropTypes from "prop-types";

import {
  FaRegClock,
  FaUser,
  FaMapPin,
  FaPhone,
  FaMoneyBillAlt
} from "react-icons/fa";
import { formatTime } from "../util/formatTime";

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
      <h4>{`Ordine #${number}`}</h4>
      <p>
        <FaRegClock /> 
        {' '}
        {formatTime(deliveryDate)}
      </p>
      <p>
        <FaUser /> 
        {' '}
        {`${firstName} ${lastName}`}
      </p>
      <p>
        <FaMapPin /> 
        {' '}
        {address}
      </p>
      <p>
        <FaPhone /> 
        {' '}
        {phone}
      </p>
      <p>
        <FaMoneyBillAlt />
        {` €${(+total).toFixed(2)} (Contanti)`}
      </p>
    </div>
  );
};

Order.propTypes = {
  number: PropTypes.string.isRequired,
  deliveryDate: PropTypes.string.isRequired,
  total: PropTypes.string.isRequired,
  firstName: PropTypes.string.isRequired,
  lastName: PropTypes.string.isRequired,
  phone: PropTypes.string.isRequired,
  address: PropTypes.string.isRequired
};

export default Order;
