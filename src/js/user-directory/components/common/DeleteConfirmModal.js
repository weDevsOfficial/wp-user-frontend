/**
 * User Directory Free - Delete Confirm Modal Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const DeleteConfirmModal = ( {isOpen, onCancel, onConfirm} ) => {
    if (!isOpen) return null;
    return (
        <div
            className="wpuf-fixed wpuf-inset-0 wpuf-z-50 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-bg-black wpuf-bg-opacity-30">
            <div
                className="wpuf-bg-white wpuf-rounded-lg wpuf-w-[660px] wpuf-h-[440px] wpuf-pt-14 wpuf-px-20 wpuf-pb-14 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-between wpuf-relative">

                {/* Close Button */}
                <button
                    onClick={onCancel}
                    className="wpuf-absolute wpuf-top-6 wpuf-right-6 wpuf-p-2 hover:wpuf-bg-gray-100 wpuf-rounded-full wpuf-transition-colors">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 13L13 1M1 1L13 13" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                </button>

                {/* Content Section */}
                <div className="wpuf-flex wpuf-flex-col wpuf-items-center">
                    <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.398438" width="109.2" height="110" rx="54.6002" fill="#FEE2E2"/>
                        <path d="M60.8792 49L60.1869 67M50.61 67L49.9177 49M69.8537 42.5811C70.5377 42.6844 71.2193 42.7949 71.8984 42.9126M69.8537 42.5811L67.718 70.3451C67.5377 72.6896 65.5827 74.5 63.2313 74.5H47.5656C45.2142 74.5 43.2592 72.6896 43.0788 70.3451L40.9431 42.5811M69.8537 42.5811C67.5609 42.2349 65.2414 41.9697 62.8984 41.7886M38.8984 42.9126C39.5776 42.7949 40.2592 42.6844 40.9431 42.5811M40.9431 42.5811C43.236 42.2349 45.5554 41.9697 47.8984 41.7886M62.8984 41.7886V39.9564C62.8984 37.5976 61.0771 35.6285 58.7196 35.553C57.6169 35.5178 56.5097 35.5 55.3984 35.5C54.2871 35.5 53.18 35.5178 52.0773 35.553C49.7197 35.6285 47.8984 37.5976 47.8984 39.9564V41.7886M62.8984 41.7886C60.4236 41.5974 57.9224 41.5 55.3984 41.5C52.8745 41.5 50.3733 41.5974 47.8984 41.7886" stroke="#EF4444" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>

                    <h1 className="wpuf-text-[30px] wpuf-font-extrabold wpuf-leading-[36px] wpuf-text-center wpuf-text-gray-600 wpuf-mt-8 wpuf-m-0">
                        {__( 'Delete Confirmation', 'wp-user-frontend' )}
                    </h1>

                    <p className="wpuf-text-[20px] wpuf-font-medium wpuf-leading-[28px] wpuf-text-center wpuf-text-gray-500 wpuf-mt-3 wpuf-m-0">
                        {__( 'Are you sure you want to delete this? This action cannot be undone.', 'wp-user-frontend' )}
                    </p>
                </div>

                {/* Buttons Section */}
                <div className="wpuf-flex wpuf-justify-center wpuf-gap-6 wpuf-mt-8">
                    <button
                        onClick={onCancel}
                        className="wpuf-w-[101px] wpuf-h-[50px] wpuf-rounded-md wpuf-border wpuf-border-gray-300 wpuf-bg-white wpuf-text-gray-700 wpuf-font-medium hover:wpuf-bg-gray-50 wpuf-pt-[13px] wpuf-pb-[13px] wpuf-pl-[25px] wpuf-pr-[23px] wpuf-text-[16px] wpuf-leading-[24px]">
                        Cancel
                    </button>

                    <button
                        onClick={onConfirm}
                        className="wpuf-w-[151px] wpuf-h-[50px] wpuf-rounded-md wpuf-bg-[#EF4444] wpuf-text-white wpuf-font-medium wpuf-shadow-sm hover:wpuf-bg-red-600 wpuf-pt-[13px] wpuf-pb-[13px] wpuf-pl-[25px] wpuf-pr-[25px] wpuf-text-[16px] wpuf-leading-[24px]"
                        style={{ boxShadow: '0px 1px 2px 0px rgba(0, 0, 0, 0.05)' }}>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    );
};

export default DeleteConfirmModal;
