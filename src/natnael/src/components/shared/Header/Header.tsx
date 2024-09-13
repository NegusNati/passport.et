import { useEffect, useRef, useState } from "react";

import { ButtonScrollTo, IconButton } from "components/core";
import MenuIcon from "icons/MenuIcon";
import { Logo } from "shared/Logo";

import { HeaderMobileMenu } from "./HeaderMobileMenu";

export const Header = () => {
  const headerRef = useRef<HTMLHeadElement>(null);
  const [isPinned, setIsPinned] = useState(false);
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  useEffect(() => {
    const { current } = headerRef;

    if (current) {
      const observerFnc = () => {
        const observer = new IntersectionObserver(
          ([entries]) => {
            setIsPinned(entries.intersectionRatio < 1);
          },
          { threshold: [1] }
        );

        observer.observe(current);

        return () => {
          observer.unobserve(current);
        };
      };

      observerFnc();

      window.addEventListener("scroll", observerFnc);
      window.addEventListener("resize", observerFnc);

      return () => {
        window.removeEventListener("scroll", observerFnc);
        window.removeEventListener("resize", observerFnc);
      };
    }
  }, []);

  return (
    <>
      <div
        className={`h-[50px] duration-200 ${isPinned && "bg-background2/75 backdrop-blur-md "}`}
      />
      <header
        className={`sticky top-[-1px] right-0 left-0 w-full z-50 duration-200 after:content-[''] after:absolute after:left-[50%] after:bottom-0 after:w-full after:translate-x-[-50%] after:h-px after:bg-border1 after:duration-200 ${
          isPinned ? "bg-background2/75 backdrop-blur-md after:scale-x-100" : "after:scale-x-0"
        }`}
        ref={headerRef}
      >
        <nav className="flex align-center gap-[16px] flex-wrap max-w-[1240px] w-full mx-auto px-[30px] py-[12px]">
          <div className="flex grow">
            <Logo />
          </div>
          <div className="hidden sm:flex sm:align-center sm:gap-[4px] sm:mx-auto">
            <ButtonScrollTo elementId="about-me">About me</ButtonScrollTo>
            <ButtonScrollTo elementId="skills">Skills</ButtonScrollTo>
            <ButtonScrollTo elementId="projects">Projects</ButtonScrollTo>
            <ButtonScrollTo elementId="contact">Contact</ButtonScrollTo>
          </div>
          <div className="flex sm:hidden">
            <IconButton title="Menu" onClick={() => setIsMenuOpen((prev) => !prev)}>
              <MenuIcon />
            </IconButton>
          </div>
        </nav>
        <HeaderMobileMenu
          isOpen={isMenuOpen}
          onClose={() => setIsMenuOpen(false)}
          isPinned={isPinned}
        />
      </header>
    </>
  );
};
