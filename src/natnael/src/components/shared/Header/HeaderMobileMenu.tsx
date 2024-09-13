import { createPortal } from "react-dom";
import FocusLock from "react-focus-lock";

import { ButtonScrollTo, IconButton } from "components/core";
import { AnimatePresence, motion } from "framer-motion";
import CloseIcon from "icons/CloseIcon";

interface HeaderMobileMenuProps {
  isOpen: boolean;
  onClose: () => void;
  isPinned: boolean;
}

export const HeaderMobileMenu = ({ isOpen, onClose, isPinned }: HeaderMobileMenuProps) => {
  return createPortal(
    <AnimatePresence>
      {isOpen && (
        <>
          <div className="fixed inset-0" onClick={onClose} />
          <FocusLock autoFocus={false}>
            <motion.div
              className={`fixed ${
                isPinned ? "top-[10px]" : "top-[60px]"
              } right-[17px] left-[17px] flex sm:hidden flex-col gap-[2px] p-[12px] rounded-md bg-background2 border border-border1 z-50 shadow-[0px_4px_7px_theme('colors.background1')] ease-out duration-100`}
              initial="inactive"
              animate="active"
              exit="inactive"
              variants={panelVariants}
            >
              <div className="flex align-center justify-end">
                <IconButton title="Close" onClick={onClose}>
                  <CloseIcon />
                </IconButton>
              </div>
              <ul className="flex flex-col w-full gap-[2px]">
                <li className="w-full [&>button]:w-full">
                  <ButtonScrollTo
                    onClick={onClose}
                    elementId="about-me"
                    textAlign="left"
                    className="w-full"
                  >
                    About me
                  </ButtonScrollTo>
                </li>
                <li className="w-full [&>button]:w-full">
                  <ButtonScrollTo
                    onClick={onClose}
                    elementId="skills"
                    textAlign="left"
                    className="w-full"
                  >
                    Skills
                  </ButtonScrollTo>
                </li>
                <li className="w-full [&>button]:w-full">
                  <ButtonScrollTo
                    onClick={onClose}
                    elementId="projects"
                    textAlign="left"
                    className="w-full"
                  >
                    Projects
                  </ButtonScrollTo>
                </li>
                <li className="w-full [&>button]:w-full">
                  <ButtonScrollTo
                    onClick={onClose}
                    elementId="contact"
                    textAlign="left"
                    className="w-full"
                  >
                    Contact
                  </ButtonScrollTo>
                </li>
              </ul>
            </motion.div>
          </FocusLock>
        </>
      )}
    </AnimatePresence>,
    document.getElementById("modals")!
  );
};

const panelVariants = {
  inactive: {
    scale: 0.85,
    opacity: 0,
    originY: 0,
    transition: { duration: 0.15 }
  },
  active: {
    scale: 1,
    opacity: 1,
    transition: { duration: 0.15 }
  }
};
