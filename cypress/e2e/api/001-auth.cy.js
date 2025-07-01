const url = `${Cypress.env("apiUrl")}/research/labels`;
const token = Cypress.env("token");
const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

context("API authorization", () => {

    it("should return 401 for no authorization present", () => {
        cy.request({
            method: "GET",
            url: url,
            headers: headers,
            failOnStatusCode: false,
        }).then((response) => {
            expect(response.status).to.eq(401);
        });
    });

    it("should return 401 for incorrect token", () => {
        cy.request({
            method: "GET",
            url: url,
            headers: {...headers, "Authorization": "Bearer incorrect"},
            failOnStatusCode: false,
        }).then((response) => {
            expect(response.status).to.eq(401);
        });
    });

    it("should return 200 for authorized user", () => {
        cy.request({
            method: "GET",
            url: url,
            headers: {...headers, "Authorization": `Bearer ${token}`},
            failOnStatusCode: false,
        }).then((res) => {
            expect(res.status).to.eq(200);
        });
    });
});
