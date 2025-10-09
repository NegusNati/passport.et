import axios from "axios";

const client = axios.create({
    baseURL: "/api/v1",
    headers: {
        Accept: "application/json",
    },
});

export const fetchPassports = (params = {}) =>
    client.get("/passports", { params }).then((response) => response.data);

export const fetchLocations = () =>
    client.get("/locations").then((response) => response.data);

export const fetchPassport = (id) =>
    client.get(`/passports/${id}`).then((response) => response.data);

export default {
    fetchPassports,
    fetchLocations,
    fetchPassport,
};
