import moment from "moment";

moment.locale("it");

export const formatTime = d => moment.utc(d).format("H:mm");

export const formatDate = d => moment.utc(d).format("LL");
