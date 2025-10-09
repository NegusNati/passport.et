import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 20,
  duration: '1m',
  thresholds: {
    http_req_duration: ['p(95)<300'],
    http_req_failed: ['rate<0.01'],
  },
};

export default function () {
  const baseUrl = __ENV.BASE_URL || 'http://app.localhost';
  const res = http.get(`${baseUrl}/api/v1/passports?per_page=25&page=1`);
  check(res, {
    'status is 200': (r) => r.status === 200,
  });
  sleep(1);
}
