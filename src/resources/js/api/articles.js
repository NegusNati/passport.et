import axios from "axios";

const client = axios.create({
    baseURL: "/api/v1",
    headers: { Accept: "application/json" },
});

export const fetchArticles = (params = {}) =>
    client.get("/articles", { params }).then((r) => r.data);

export const fetchArticle = (slug) =>
    client.get(`/articles/${slug}`).then((r) => r.data);

export const fetchCategories = () =>
    client.get(`/categories`).then((r) => r.data);

export const fetchTags = () =>
    client.get(`/tags`).then((r) => r.data);

export default { fetchArticles, fetchArticle, fetchCategories, fetchTags };

