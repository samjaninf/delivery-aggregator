import React from "react";
import { Switch } from "react-router-dom";

import PrivateRoute from "./PrivateRoute";
import App from "../pages/App";
import Login from "../pages/Login";
import Logout from "../pages/Logout";

const Routes = () => (
  <Switch>
    <PrivateRoute path="/login" inverted component={Login} />

    <PrivateRoute path="/" exact component={App} />
    <PrivateRoute path="/logout" exact component={Logout} />
  </Switch>
);

export default Routes;
