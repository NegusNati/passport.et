import svg from "../../../../public/negus.svg";
export const Logo = () => {
  const scrollToTop = () =>
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth"
    });

  return (
    <button
      className="text-color1 font-bold text-2xl sm:text-3xl duration-200 sm:hover:text-negus focus-visible:text-primary"
      onClick={scrollToTop}
    >
      <a href="/" className="flex items-center">
        {" "}
        <span>
          <img src={svg} alt="logo svg" className="w-10 h-10 mr-2" />
        </span>
        Portfolio{" "}
      </a>
    </button>
  );
};
