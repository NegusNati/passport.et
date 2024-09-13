import { useEffect, useMemo } from "react";

import { DefaultTemplate } from "components/default-template";

const DefaultPage = () => {
  const observer = useMemo(
    () =>
      new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("animate-show");
          } else {
            entry.target.classList.remove("animate-show");
          }
        });
      }),
    []
  );

  useEffect(() => {
    const animateElements = document.querySelectorAll(".animate-hidden");

    animateElements.forEach((el) => observer.observe(el));

    return () => {
      animateElements.forEach((el) => observer.unobserve(el));
    };
  }, [observer]);

  return <DefaultTemplate />;
};

export default DefaultPage;
