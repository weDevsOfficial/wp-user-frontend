/**
 * User Directory Free - Directory Wizard Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React, { useState, useRef, useEffect } from 'react';
import { __ } from '@wordpress/i18n';

import StepBasics from './steps/StepBasics';
import StepLayout from './steps/StepLayout';
import StepProfile from './steps/StepProfile';
import StepTabs from './steps/StepTabs';
import StepAdvanced from './steps/StepAdvanced';
import Toast from './common/Toast';

// Default form values for free version
const DEFAULTS = {
    directory_title: '',
    directory_layout: 'layout-3',
    profile_layout: 'layout-2',
    per_page: 12,
    users_per_row: 3,
    orderby: 'ID',
    order: 'DESC',
    avatar_size: '192',
    profile_size: 'medium',
    enable_search: true,
    roles: [],
    // Profile tabs - all enabled by default (consistent with Pro)
    profile_tabs: ['about', 'posts', 'files', 'comments'],
    profile_tabs_order: ['about', 'posts', 'files', 'comments'],
};

const DirectoryWizard = ({ onClose, initialData, config = {} }) => {
    const [formData, setFormData] = useState({ ...DEFAULTS });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [toastMessage, setToastMessage] = useState(null);

    // Step definitions for Free version
    const steps = [
        { id: 'basics', label: __('Directory Basics', 'wp-user-frontend') },
        { id: 'layout', label: __('Directory Layout', 'wp-user-frontend') },
        { id: 'profile', label: __('Profile Layout', 'wp-user-frontend') },
        { id: 'content', label: __('Profile Content', 'wp-user-frontend') },
        { id: 'advanced', label: __('Advanced Control', 'wp-user-frontend') },
    ];
    
    const [currentStepIndex, setCurrentStepIndex] = useState(0);
    const [fade, setFade] = useState(true);
    const prevStepIndex = useRef(0);

    useEffect(() => {
        if (initialData && initialData.post_content) {
            try {
                const parsedContent = JSON.parse(initialData.post_content || '{}');
                setFormData({
                    ...DEFAULTS,
                    directory_title: initialData.post_title || '',
                    ...parsedContent,
                    ID: initialData.ID,
                });
            } catch (e) {
                setFormData({
                    ...DEFAULTS,
                    directory_title: initialData.post_title || '',
                    ID: initialData.ID,
                });
            }
        }
    }, [initialData]);

    // Navigation between steps
    const navigateToStep = (stepIndex) => {
        setFade(false);
        setTimeout(() => {
            prevStepIndex.current = currentStepIndex;
            setCurrentStepIndex(stepIndex);
            setFade(true);
        }, 200);
    };

    const handleNextStep = () => {
        const isLastStep = currentStepIndex === steps.length - 1;
            
        if (!isLastStep) {
            setFade(false);
            setTimeout(() => {
                prevStepIndex.current = currentStepIndex;
                setCurrentStepIndex(currentStepIndex + 1);
                setFade(true);
            }, 200);
        } else {
            handleSubmit();
        }
    };

    const handlePrevStep = () => {
        if (currentStepIndex > 0) {
            setFade(false);
            setTimeout(() => {
                prevStepIndex.current = currentStepIndex;
                setCurrentStepIndex(currentStepIndex - 1);
                setFade(true);
            }, 200);
        }
    };

    const handleSubmit = async () => {
        setLoading(true);
        setError('');
        
        try {
            const restUrl = config.rest_url || '/wp-json/';
            const url = formData.ID
                ? `${restUrl}wpuf/v1/user_directory/${formData.ID}`
                : `${restUrl}wpuf/v1/user_directory`;
            
            const response = await fetch(url, {
                method: formData.ID ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.rest_nonce || '',
                },
                body: JSON.stringify(formData),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || __('Something went wrong', 'wp-user-frontend'));
            }
            
            setToastMessage({ text: __('Directory settings saved successfully!', 'wp-user-frontend'), type: 'success' });
            setLoading(false);
            
            setTimeout(() => {
                onClose();
            }, 1500);
        } catch (err) {
            setError(err.message || __('Something went wrong', 'wp-user-frontend'));
            setLoading(false);
        }
    };

    const handleSaveAndExit = async () => {
        await handleSubmit();
    };

    // Render the current step component
    const renderStepComponent = () => {
        const stepProps = { formData, setFormData, config };
        
        switch (currentStepIndex) {
            case 0:
                return <StepBasics {...stepProps} />;
            case 1:
                return <StepLayout {...stepProps} />;
            case 2:
                return <StepProfile {...stepProps} />;
            case 3:
                return <StepTabs {...stepProps} />;
            case 4:
                return <StepAdvanced {...stepProps} />;
            default:
                return null;
        }
    };

    const isLastStep = currentStepIndex === steps.length - 1;

    return (
        <div className="wpuf-directory-wizard wpuf-w-full wpuf-pb-24">
            <div className="wpuf-flex wpuf-justify-between wpuf-m-8">
                <a href="#" onClick={(e) => { e.preventDefault(); onClose(); }} className="wpuf-flex wpuf-items-center wpuf-text-gray-500 hover:wpuf-text-gray-700 wpuf-no-underline">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="wpuf-mr-1">
                        <path d="M15.8332 10.0001H4.1665M4.1665 10.0001L9.99984 15.8334M4.1665 10.0001L9.99984 4.16675" stroke="currentColor" strokeWidth="1.67" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <span className="wpuf-text-gray-700 wpuf-text-base wpuf-font-medium">{__('Directory List', 'wp-user-frontend')}</span>
                </a>

                <button
                    onClick={handleSaveAndExit}
                    className="wpuf-px-4 wpuf-py-2 wpuf-text-gray-700 wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-md hover:wpuf-bg-gray-50 wpuf-transition-colors wpuf-flex wpuf-items-center"
                    disabled={loading}
                >
                    {loading && (
                        <span className="wpuf-animate-spin wpuf-mr-2 wpuf-inline-block wpuf-w-4 wpuf-h-4 wpuf-border-2 wpuf-border-gray-600 wpuf-border-t-transparent wpuf-rounded-full"></span>
                    )}
                    { loading ? __( 'Saving...', 'wp-user-frontend' ) : __( 'Save and Exit', 'wp-user-frontend' ) }
                </button>
            </div>

            <div className="wpuf-flex wpuf-justify-center">
                <div className="wpuf-w-2/3 wpuf-max-w-2/3">
                    <p className="wpuf-text-center wpuf-text-3xl wpuf-font-normal wpuf-text-gray-900">{__( 'Set up your Directory', 'wp-user-frontend' )}</p>

                    <nav aria-label="Progress" className="wpuf-flex wpuf-justify-center wpuf-mt-10">
                        <ol role="list" className="wpuf-flex wpuf-items-center wpuf-list-none wpuf-p-0 wpuf-m-0">
                            {steps.map((step, index) => (
                                <li key={step.id} className="wpuf-relative wpuf-list-none">
                                    <div className={`wpuf-relative ${index !== steps.length - 1 ? 'wpuf-pr-40' : ''}`}>
                                        {/* Connector Line */}
                                        {index !== steps.length - 1 && (
                                            <div className="wpuf-absolute wpuf-inset-0 wpuf-flex wpuf-items-center" aria-hidden="true">
                                                <div className={`wpuf-h-0.5 wpuf-w-full ${index < currentStepIndex ? 'wpuf-bg-emerald-600' : 'wpuf-bg-gray-200'}`}></div>
                                            </div>
                                        )}
                                        
                                        {/* Step Circle */}
                                        <a
                                            href="#"
                                            onClick={(e) => { e.preventDefault(); navigateToStep(index); }}
                                            className={`wpuf-relative wpuf-flex wpuf-items-center wpuf-justify-center focus:wpuf-shadow-none ${
                                                index <= currentStepIndex 
                                                    ? '' 
                                                    : 'wpuf-border-2 wpuf-border-gray-300 wpuf-bg-white hover:wpuf-border-gray-400'
                                            }`}
                                            style={{ 
                                                width: '24px', 
                                                height: '24px', 
                                                borderRadius: '23px',
                                                opacity: 1,
                                                backgroundColor: index <= currentStepIndex ? '#059669' : ''
                                            }}
                                        >
                                            <svg width="9" height="7" viewBox="0 0 9 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                            </svg>
                                        </a>
                                        
                                        {/* Step Label */}
                                        <span 
                                            className="wpuf-w-max wpuf-absolute wpuf-left-[-35px] wpuf-top-8 wpuf-flex wpuf-items-center wpuf-justify-center"
                                            style={{ 
                                                fontWeight: 400,
                                                fontSize: '14px',
                                                lineHeight: '20px',
                                                letterSpacing: '0%',
                                                textAlign: 'center',
                                                color: index <= currentStepIndex ? '#059669' : '#374151'
                                            }}
                                        >
                                            {step.label}
                                        </span>
                                    </div>
                                </li>
                            ))}
                        </ol>
                    </nav>
                </div>
            </div>

            {/* Step Content */}
            <div className="wpuf-mt-16 wpuf-mx-auto wpuf-w-full wpuf-max-w-4xl wpuf-px-4">
                <div className={`wpuf-transition-opacity wpuf-duration-200 wpuf-ease-in-out ${fade ? 'wpuf-opacity-100' : 'wpuf-opacity-0'}`}>
                    {error && (
                        <div className="wpuf-mb-4 wpuf-text-red-600 wpuf-bg-red-50 wpuf-p-3 wpuf-rounded-md">
                            {error}
                        </div>
                    )}
                    {renderStepComponent()}
                </div>
            </div>

            {/* Footer Navigation */}
            <div className="wpuf-fixed wpuf-bottom-0 wpuf-left-0 wpuf-right-0 wpuf-bg-white wpuf-border-t wpuf-border-gray-200 wpuf-shadow-lg wpuf-z-50"
                 style={{ left: document.body.classList.contains('folded') ? '36px' : '160px' }}>
                <div className="wpuf-container wpuf-mx-auto">
                    <div className="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-max-w-4xl wpuf-mx-auto wpuf-py-4 wpuf-px-4">
                        {/* Left buttons */}
                        <div className="wpuf-flex wpuf-items-center wpuf-gap-3">
                            {currentStepIndex === 0 ? (
                                <button
                                    onClick={onClose}
                                    className="wpuf-px-4 wpuf-py-2 wpuf-text-gray-700 wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-md hover:wpuf-bg-gray-50 wpuf-transition-colors"
                                >
                                    {__('Cancel', 'wp-user-frontend')}
                                </button>
                            ) : (
                                <button
                                    onClick={handlePrevStep}
                                    className="wpuf-px-4 wpuf-py-2 wpuf-text-gray-700 wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-md hover:wpuf-bg-gray-50 wpuf-transition-colors wpuf-flex wpuf-items-center"
                                >
                                    <svg className="wpuf-w-4 wpuf-h-4 wpuf-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                    </svg>
                                    {__('Previous', 'wp-user-frontend')}
                                </button>
                            )}
                        </div>

                        {/* Right buttons */}
                        <div>
                            <button
                                onClick={handleNextStep}
                                disabled={loading}
                                className="wpuf-px-6 wpuf-py-2 wpuf-text-white wpuf-bg-emerald-600 wpuf-rounded-md hover:wpuf-bg-emerald-700 wpuf-transition-colors wpuf-flex wpuf-items-center disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed"
                            >
                                {loading && isLastStep && (
                                    <span className="wpuf-animate-spin wpuf-mr-2 wpuf-inline-block wpuf-w-4 wpuf-h-4 wpuf-border-2 wpuf-border-white wpuf-border-t-transparent wpuf-rounded-full"></span>
                                )}
                                {isLastStep
                                    ? (loading ? __('Saving...', 'wp-user-frontend') : __('Save Changes', 'wp-user-frontend'))
                                    : __('Next', 'wp-user-frontend')
                                }
                                {!loading && !isLastStep && (
                                    <svg className="wpuf-w-4 wpuf-h-4 wpuf-ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                    </svg>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Toast */}
            {toastMessage && (
                <Toast 
                    message={toastMessage.text} 
                    type={toastMessage.type} 
                    onClose={() => setToastMessage(null)}
                />
            )}
        </div>
    );
};

export default DirectoryWizard;
