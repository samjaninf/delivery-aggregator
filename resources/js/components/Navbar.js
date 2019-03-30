import React from "react";
import { NavLink } from "react-router-dom";

const Navbar = () => (
  <nav>
    <h1>Delivery Aggregator</h1>
    <ul>
      <li>
        <NavLink to="/">Home</NavLink>
      </li>
      <li>
        <NavLink to="/logout">Logout</NavLink>
      </li>
    </ul>
  </nav>
);

export default Navbar;
